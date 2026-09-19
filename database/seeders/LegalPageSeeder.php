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
<p><strong>Effective Date:</strong> September 16, 2026 | <strong>Last Updated:</strong> September 16, 2026</p>

<h3>1. Introduction</h3>
<p>KTS Markets ("we," "our," or "us") provides trading education, technical market insights, and analytical bot tools through our mobile application and online platform (collectively, the "Services"). We respect user privacy and are committed to safeguarding personal data in compliance with international standards, GDPR, CCPA, and Google Play Developer Program Policies.</p>

<h3>2. Information We Collect</h3>
<h4>A. Personal Information You Provide:</h4>
<ul>
<li><strong>Account Registration:</strong> Full Name, Email address, password (bcrypt-hashed), and profile avatar.</li>
<li><strong>Contact Details:</strong> Phone / WhatsApp number (optional, for customer support and verification).</li>
<li><strong>Security Verification:</strong> Account recovery security codes.</li>
</ul>

<h4>B. Trading & Analytics Configuration:</h4>
<ul>
<li><strong>Terminal Integration:</strong> MT5 account number, broker server name, and trader read-only configurations (used solely for bot tracking, trade telemetry, and performance analytics).</li>
<li><strong>Demo Requests:</strong> Demo account preferences and trading experience level.</li>
</ul>

<h4>C. Automatically Collected Technical Data:</h4>
<ul>
<li><strong>Device & OS:</strong> Device model, manufacturer, Android OS version, and app version.</li>
<li><strong>Push Notification Tokens:</strong> Firebase Cloud Messaging (FCM) device tokens to deliver real-time educational signals and system alerts.</li>
<li><strong>Diagnostics:</strong> IP address, approximate city/country, and crash diagnostics.</li>
</ul>

<h4>D. What We Explicitly DO NOT Collect:</h4>
<ul>
<li>Financial account passwords or bank login credentials.</li>
<li>Credit/debit card numbers or payment card CVVs.</li>
<li>Biometric data (fingerprints, facial recognition data).</li>
<li>Precise real-time GPS location tracking.</li>
</ul>

<h3>3. How We Use Your Information</h3>
<ul>
<li><strong>Educational & Signal Delivery:</strong> Providing real-time educational market signals, technical charting insights, and academy courses.</li>
<li><strong>Bot Analytics & Performance:</strong> Tracking MT5 bot configurations, equity curves, and performance statistics.</li>
<li><strong>Account Security:</strong> Authenticating logins, preventing duplicate registrations, and protecting user accounts.</li>
<li><strong>Push Alerts:</strong> Delivering real-time market updates, course releases, and support notifications.</li>
<li><strong>AI Chatbot Assistance:</strong> Processing user queries via Groq AI to deliver contextual educational responses.</li>
<li><strong>Compliance:</strong> Enforcing our Terms of Service and adhering to digital privacy laws.</li>
</ul>

<h3>4. Data Sharing & Third-Party Service Providers</h3>
<p>We engage trusted third-party infrastructure providers under strict confidentiality and security terms:</p>
<ul>
<li><strong>Google Firebase:</strong> Push notification delivery (FCM), crash reporting, and Google OAuth sign-in.</li>
<li><strong>Groq AI:</strong> High-speed API processing for educational AI chatbot inquiries.</li>
<li><strong>Railway & Cloud Hosting:</strong> Secure encrypted backend hosting, database management, and API servers.</li>
</ul>
<p><strong>Strict No-Sale Clause:</strong> We NEVER sell, rent, monetize, or trade your personal data to third-party advertisers, data brokers, or marketing networks.</p>

<h3>5. Data Security & Encryption</h3>
<ul>
<li><strong>In-Transit Encryption:</strong> All data is transmitted securely using Transport Layer Security (TLS 1.3 / HTTPS).</li>
<li><strong>At-Rest Encryption:</strong> Database records, server storage, and backups are protected with AES-256 encryption.</li>
<li><strong>Password Hashing:</strong> Passwords are cryptographically hashed using Bcrypt; plain-text passwords are never stored.</li>
<li><strong>Access Control:</strong> Strict role-based access control (RBAC) ensuring only authorized administrative personnel can access support records.</li>
</ul>

<h3>6. Account and Data Deletion Policy (Google Play Compliant)</h3>
<p>In full compliance with Google Play's User Data and Account Deletion policy, users have the absolute right to permanently delete their account and associated data at any time:</p>
<ul>
<li><strong>In-App Deletion (Instant):</strong> Open the KTS Markets app &gt; Profile &gt; Settings &gt; Tap <strong>"Delete Account"</strong> and confirm. Your account and personal data are immediately deleted.</li>
<li><strong>Web Deletion Request:</strong> Visit our direct web deletion URL: <a href="https://kts-backend-production.up.railway.app/delete-account" target="_blank">https://kts-backend-production.up.railway.app/delete-account</a> or email <a href="mailto:privacy@ktsmarkets.com">privacy@ktsmarkets.com</a>.</li>
<li><strong>Data Purged:</strong> Profile details, email, phone, authentication credentials, MT5 IDs, chat history, and notification tokens are completely expunged. Residual backups are erased within 30 days.</li>
</ul>

<h3>7. Data Retention Schedule</h3>
<ul>
<li><strong>Active Accounts:</strong> Retained for the lifetime of your active account until you request deletion.</li>
<li><strong>AI Chat Logs:</strong> Stored for up to 90 days for quality assurance, then permanently deleted.</li>
<li><strong>Support Tickets:</strong> Archived for 12 months after resolution, then deleted.</li>
<li><strong>Push Tokens:</strong> Automatically invalidated and removed upon app uninstallation.</li>
</ul>

<h3>8. Your Privacy Rights (GDPR, CCPA & Global)</h3>
<p>You have the right to access, rectify, port, restrict, or completely delete your personal data. To exercise any privacy rights, contact our Data Protection team at <strong>privacy@ktsmarkets.com</strong>. All requests are processed within 30 days free of charge.</p>

<h3>9. Age Limitations (Strict 18+ Policy)</h3>
<p>KTS Markets is strictly designed for individuals aged <strong>18 and older</strong>. We do not knowingly collect personal data from anyone under 18 years of age. If we learn that an individual under 18 has registered an account, we will immediately delete their account and all associated information.</p>

<h3>10. Financial Risk & Educational Disclaimer</h3>
<p><strong>IMPORTANT:</strong> KTS Markets is an educational technology and market analytics platform. All materials, trading signals, course videos, charting patterns, bot analytics, and AI assistant insights are provided strictly for educational, informational, and research purposes. They do NOT constitute financial, investment, or legal advice. Trading Forex, CFDs, and cryptocurrencies carries substantial risk of loss. Past performance does not guarantee future results. KTS Markets is NOT a registered financial broker or fund manager and does not hold user funds.</p>

<h3>11. Contact Information</h3>
<p>For any privacy inquiries or compliance requests, contact us at:</p>
<p><strong>KTS Markets Compliance Team</strong><br>
Email: <a href="mailto:privacy@ktsmarkets.com">privacy@ktsmarkets.com</a><br>
Support: <a href="mailto:support@ktsmarkets.com">support@ktsmarkets.com</a><br>
Website: <a href="https://ktsmarkets.com" target="_blank">https://ktsmarkets.com</a><br>
WhatsApp: +92 337 1244640</p>
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
