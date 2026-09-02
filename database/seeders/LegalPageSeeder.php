<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'summary' => 'How KTS Markets collects, uses, and protects your personal information.',
                'content' => $this->getPrivacyPolicy(),
                'is_active' => true,
                'last_published_at' => now(),
            ],
            [
                'slug' => 'terms-conditions',
                'title' => 'Terms & Conditions',
                'summary' => 'Terms of use for the KTS Markets application and services.',
                'content' => $this->getTermsConditions(),
                'is_active' => true,
                'last_published_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }

    private function getPrivacyPolicy(): string
    {
        return <<<HTML
<h2>Privacy Policy for KTS Markets</h2>
<p><strong>Last Updated:</strong> September 2, 2026</p>

<h3>1. Introduction</h3>
<p>KTS Markets ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our mobile application and services.</p>

<h3>2. Information We Collect</h3>
<h4>Personal Information:</h4>
<ul>
<li>Name and email address (when you register)</li>
<li>WhatsApp number (for communication)</li>
<li>MT5 Account ID (for bot linking services)</li>
<li>Profile information you provide (city, country, avatar)</li>
</ul>

<h4>Automatically Collected Information:</h4>
<ul>
<li>Device type and operating system</li>
<li>App usage data and analytics</li>
<li>Push notification tokens (for delivering notifications)</li>
</ul>

<h3>3. How We Use Your Information</h3>
<ul>
<li>To provide and maintain our trading services</li>
<li>To link your MT5 account with our trading bots</li>
<li>To send you trading signals and notifications</li>
<li>To provide AI-powered chat assistance</li>
<li>To communicate with you about your account</li>
<li>To improve our application and services</li>
</ul>

<h3>4. Data Sharing</h3>
<p>We may share your information with:</p>
<ul>
<li><strong>Service Providers:</strong> We use third-party services including Groq (AI processing), Firebase (push notifications), and hosting providers.</li>
<li><strong>Legal Requirements:</strong> We may disclose your information if required by law or in response to valid requests by public authorities.</li>
</ul>

<h3>5. Data Security</h3>
<p>We implement industry-standard security measures including:</p>
<ul>
<li>Encryption of data in transit (HTTPS/TLS)</li>
<li>Secure token-based authentication</li>
<li>Regular security audits</li>
</ul>

<h3>6. Data Retention</h3>
<p>We retain your personal information only for as long as necessary to provide our services. When you delete your account, all associated personal data is permanently removed from our systems.</p>

<h3>7. Your Rights</h3>
<ul>
<li><strong>Access:</strong> You can view your profile data within the app.</li>
<li><strong>Correction:</strong> You can update your profile information at any time.</li>
<li><strong>Deletion:</strong> You can permanently delete your account through the app's Profile section.</li>
<li><strong>Opt-out:</strong> You can disable push notifications in your device settings.</li>
</ul>

<h3>8. AI Chatbot</h3>
<p>Our AI chatbot feature processes your messages through Groq's AI services. Messages are used to provide responses and may be logged for service improvement. You can report inappropriate AI responses through the in-app reporting feature.</p>

<h3>9. Children's Privacy</h3>
<p>Our services are not intended for users under the age of 18. We do not knowingly collect personal information from children.</p>

<h3>10. Changes to This Policy</h3>
<p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy within the app.</p>

<h3>11. Contact Us</h3>
<p>If you have questions about this Privacy Policy, please contact us at:</p>
<p><strong>KTS (Khan Trading School)</strong><br>
Email: support@ktsmarkets.com</p>
HTML;
    }

    private function getTermsConditions(): string
    {
        return <<<HTML
<h2>Terms & Conditions</h2>
<p><strong>Last Updated:</strong> September 2, 2026</p>

<h3>1. Acceptance of Terms</h3>
<p>By accessing and using the KTS Markets application ("App"), you accept and agree to be bound by these Terms and Conditions.</p>

<h3>2. Description of Services</h3>
<p>KTS Markets provides:</p>
<ul>
<li>Forex and Gold trading signals</li>
<li>MT5 automated trading bot (KTS Bot)</li>
<li>Market analysis and education</li>
<li>AI-powered trading assistance</li>
</ul>

<h3>3. Financial Risk Disclosure</h3>
<p><strong>IMPORTANT:</strong> Trading foreign exchange, commodities, and CFDs carries a high level of risk and may not be suitable for all investors. Past performance is not indicative of future results. The possibility exists that you could sustain a loss of some or all of your initial investment.</p>
<ul>
<li>Automated trading bots are configured tools, not guarantees of profit</li>
<li>The 1% profit target and 5% loss limit are configured settings, not guarantees</li>
<li>Actual losses may exceed the configured limits due to slippage, gaps, or market conditions</li>
<li>You should not invest money that you cannot afford to lose</li>
</ul>

<h3>4. User Responsibilities</h3>
<ul>
<li>You are responsible for maintaining the confidentiality of your account credentials</li>
<li>You must provide accurate registration information</li>
<li>You agree to use the services only for lawful purposes</li>
<li>You understand that trading decisions are your own responsibility</li>
</ul>

<h3>5. Account Deletion</h3>
<p>You may delete your account at any time through the app's Profile section. Upon deletion, all your personal data will be permanently removed from our systems.</p>

<h3>6. Intellectual Property</h3>
<p>All content, features, and functionality of the App are owned by KTS Markets and are protected by copyright, trademark, and other intellectual property laws.</p>

<h3>7. Limitation of Liability</h3>
<p>KTS Markets shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the services.</p>

<h3>8. Changes to Terms</h3>
<p>We reserve the right to modify these Terms at any time. Continued use of the App after changes constitutes acceptance of the new Terms.</p>

<h3>9. Contact</h3>
<p>For questions about these Terms, contact: support@ktsmarkets.com</p>
HTML;
    }
}
