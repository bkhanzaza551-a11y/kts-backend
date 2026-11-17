<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PostgresSupportAndSchemaIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure on-the-fly connection to our running isolated PostgreSQL test instance
        config([
            'database.connections.pgsql_isolated' => [
                'driver' => 'pgsql',
                'host' => '127.0.0.1',
                'port' => '5432',
                'database' => 'kts_test_postgres',
                'username' => 'businessos',
                'password' => 'local_dev_change_me',
                'charset' => 'utf8',
                'prefix' => '',
                'search_path' => 'public',
                'sslmode' => 'prefer',
            ]
        ]);
    }

    public function test_postgresql_schema_columns_and_data_types()
    {
        $connection = DB::connection('pgsql_isolated');

        // Check columns exist on support_tickets
        $hasSource = $connection->select("
            SELECT column_name, data_type, column_default, is_nullable
            FROM information_schema.columns 
            WHERE table_name = 'support_tickets' AND column_name = 'source'
        ");

        $this->assertNotEmpty($hasSource, 'Column source must exist in PostgreSQL support_tickets');
        $this->assertEquals('character varying', $hasSource[0]->data_type);

        // Check columns exist on support_ticket_replies
        $hasIsSystem = $connection->select("
            SELECT column_name, data_type, column_default, is_nullable
            FROM information_schema.columns 
            WHERE table_name = 'support_ticket_replies' AND column_name = 'is_system'
        ");

        $this->assertNotEmpty($hasIsSystem, 'Column is_system must exist in PostgreSQL support_ticket_replies');
        $this->assertEquals('boolean', $hasIsSystem[0]->data_type);

        $hasDeletedAt = $connection->select("
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns 
            WHERE table_name = 'support_ticket_replies' AND column_name = 'deleted_at'
        ");

        $this->assertNotEmpty($hasDeletedAt, 'Column deleted_at must exist in PostgreSQL support_ticket_replies');
        $this->assertEquals('timestamp without time zone', $hasDeletedAt[0]->data_type);
        $this->assertEquals('YES', $hasDeletedAt[0]->is_nullable);
    }

    public function test_postgresql_support_ticket_and_reply_crud_lifecycle()
    {
        $connection = DB::connection('pgsql_isolated');

        // 1. Ensure a user exists in pgsql_isolated
        $userRow = $connection->table('users')->where('email', 'pg_test_user@ktsmarkets.com')->first();
        if (!$userRow) {
            $userId = $connection->table('users')->insertGetId([
                'name' => 'Postgres Test User',
                'email' => 'pg_test_user@ktsmarkets.com',
                'password' => bcrypt('Password123!'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $userRow->id;
        }

        // 2. Create Support Ticket with source = 'mobile'
        $ticketId = $connection->table('support_tickets')->insertGetId([
            'ticket_number' => 'TICK-PG-' . rand(10000, 99999),
            'user_id' => $userId,
            'subject' => 'PG Test Ticket ' . uniqid(),
            'description' => 'Testing PostgreSQL direct insertion and constraints',
            'status' => 'open',
            'priority' => 'medium',
            'source' => 'mobile',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertGreaterThan(0, $ticketId);

        // Verify ticket source
        $savedTicket = $connection->table('support_tickets')->where('id', $ticketId)->first();
        $this->assertEquals('mobile', $savedTicket->source);

        // 3. Create System Auto-Reply
        $systemReplyId = $connection->table('support_ticket_replies')->insertGetId([
            'support_ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => 'Thank you for reaching out. We received your request.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $savedSystemReply = $connection->table('support_ticket_replies')->where('id', $systemReplyId)->first();
        $this->assertTrue((bool)$savedSystemReply->is_system);

        // 4. Create User/Admin Reply
        $adminReplyId = $connection->table('support_ticket_replies')->insertGetId([
            'support_ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => 'Admin is reviewing your issue.',
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $savedAdminReply = $connection->table('support_ticket_replies')->where('id', $adminReplyId)->first();
        $this->assertFalse((bool)$savedAdminReply->is_system);

        // 5. Test Soft Deletes
        $connection->table('support_ticket_replies')
            ->where('id', $adminReplyId)
            ->update(['deleted_at' => now()]);

        $activeReplies = $connection->table('support_ticket_replies')
            ->where('support_ticket_id', $ticketId)
            ->whereNull('deleted_at')
            ->get();

        $this->assertCount(1, $activeReplies);
        $this->assertEquals($systemReplyId, $activeReplies[0]->id);

        // 6. Test Model Hydration & Casts
        $modelReply = SupportTicketReply::on('pgsql_isolated')->find($systemReplyId);
        $this->assertNotNull($modelReply);
        $this->assertIsBool($modelReply->is_system);
        $this->assertTrue($modelReply->is_system);

        // 7. Clean up
        $connection->table('support_ticket_replies')->where('support_ticket_id', $ticketId)->delete();
        $connection->table('support_tickets')->where('id', $ticketId)->delete();
    }
}
