<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSupportAndChatTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        $admin = User::where('email', 'admin@ktsmarkets.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'KTS Admin',
                'email' => 'admin@ktsmarkets.com',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
            ]);
        }

        Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Administrator', 'description' => 'Full administrative access']
        );

        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        return $admin;
    }

    public function test_support_ticket_e2e_lifecycle(): void
    {
        $admin = $this->getSuperAdmin();

        $user = User::create([
            'name' => 'Support Seeker',
            'email' => 'seeker_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        // 1. Mobile user creates support ticket
        $createTicketResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/support/tickets', [
                'subject' => 'Issue with MT5 Bot Connection',
                'description' => 'The bot stopped placing trades on EURUSD today.',
                'priority' => 'high',
                'source' => 'manual',
            ]);
        $createTicketResponse->assertSuccessful();
        $ticketId = $createTicketResponse->json('data.id');
        $this->assertNotNull($ticketId);

        $ticket = SupportTicket::find($ticketId);
        $this->assertNotNull($ticket);

        // 2. Admin views tickets list and ticket details
        $adminListResponse = $this->actingAs($admin, 'web')->get(route('admin.support-chat.index'));
        $adminListResponse->assertStatus(200);

        $adminShowResponse = $this->actingAs($admin, 'web')->get(route('admin.support-chat.show', $ticket));
        $adminShowResponse->assertStatus(200);

        // 3. Admin replies to ticket
        $adminReplyResponse = $this->actingAs($admin, 'web')->post(route('admin.support-chat.reply', $ticket), [
            'message' => 'Hello, please verify your MT5 investor password and DLL imports setting.',
        ]);
        $adminReplyResponse->assertSessionHasNoErrors();

        // 4. Mobile user views ticket and sees admin reply
        $mobileShowResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/support/tickets/{$ticketId}");
        $mobileShowResponse->assertStatus(200);

        // 5. Admin closes ticket
        $adminCloseResponse = $this->actingAs($admin, 'web')->post(route('admin.support-chat.close', $ticket));
        $adminCloseResponse->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
    }

    public function test_support_ticket_duplicate_subject_prevention(): void
    {
        $user = User::create([
            'name' => 'Duplicate Tester',
            'email' => 'duplicate_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'subject' => 'Withdrawal Delay Question',
            'description' => 'I made a withdrawal request 2 hours ago.',
            'priority' => 'medium',
            'source' => 'manual',
        ];

        // First ticket succeeds
        $firstResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/support/tickets', $payload);
        $firstResponse->assertSuccessful();

        // Second ticket with same subject while open is rejected (422)
        $secondResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/support/tickets', $payload);
        $secondResponse->assertStatus(422);
        $secondResponse->assertJsonFragment(['success' => false]);
    }

    public function test_unauthorized_user_cannot_view_other_users_ticket(): void
    {
        $userA = User::create([
            'name' => 'User Alpha',
            'email' => 'alpha_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $tokenA = $userA->createToken('test-token')->plainTextToken;

        $userB = User::create([
            'name' => 'User Beta',
            'email' => 'beta_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $tokenB = $userB->createToken('test-token')->plainTextToken;

        $createResponse = $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->postJson('/api/v1/support/tickets', [
                'subject' => 'Confidential Financial Dispute',
                'description' => 'Dispute transaction ID #9988',
                'priority' => 'high',
                'source' => 'manual',
            ]);
        $createResponse->assertSuccessful();
        $ticketId = $createResponse->json('data.id');

        // User B attempts to access User A's ticket
        auth('sanctum')->forgetUser();
        $accessResponse = $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/support/tickets/{$ticketId}");
        $this->assertTrue(in_array($accessResponse->status(), [403, 404]));
    }

    public function test_support_chat_index_view_regression_for_route_not_found(): void
    {
        $admin = $this->getSuperAdmin();

        // Regression test for FIX-03: previously failed with RouteNotFoundException for admin.ai-chatbot.index
        $response = $this->actingAs($admin, 'web')->get(route('admin.support-chat.index'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.ai-chatbot.settings'));
        $response->assertDontSee('Route [admin.ai-chatbot.index] not defined');
    }

    public function test_community_chat_moderation(): void
    {
        $admin = $this->getSuperAdmin();

        $room = ChatRoom::firstOrCreate(
            ['slug' => 'general-trading'],
            ['name' => 'General Trading', 'is_active' => true]
        );

        $user = User::create([
            'name' => 'Chatter Spammer',
            'email' => 'spammer_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        // 1. User sends message
        $sendResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/chat/rooms/{$room->id}/messages", [
                'type' => 'text',
                'message' => 'Buy my scam coin now!',
            ]);
        $sendResponse->assertStatus(201);
        $messageId = $sendResponse->json('data.id');

        $message = ChatMessage::find($messageId);
        $this->assertNotNull($message);

        // 2. Admin deletes message
        $deleteResponse = $this->actingAs($admin, 'web')->delete(route('admin.chat.destroy-message', $message));
        $deleteResponse->assertSessionHasNoErrors();

        // 3. Admin bans user from chat
        $banResponse = $this->actingAs($admin, 'web')->post(route('admin.chat.ban-user'), [
            'user_id' => $user->id,
            'reason' => 'Spamming scam coins',
            'duration_hours' => 24,
        ]);
        $banResponse->assertSessionHasNoErrors();
    }
}
