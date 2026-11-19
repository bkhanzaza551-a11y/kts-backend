<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to KTS Markets</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f3f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1e293b;">

  <!-- Outer Background Table -->
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f3f6; padding: 36px 12px;">
    <tr>
      <td align="center">

        <!-- Main Card Container (max-width 580px) -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 580px; margin: 0 auto;">

          <!-- TOP BRAND HEADER WITH REAL KTS LOGO -->
          <tr>
            <td align="center" style="padding-bottom: 24px;">
              <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center">
                      <img src="https://raw.githubusercontent.com/bkhanzaza551-a11y/kts-backend/main/public/images/kts-logo.jpg" width="88" height="88" alt="KTS Markets" style="display: block; border-radius: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.18); margin: 0 auto; border: 0;" />
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding-top: 12px;">
                    <div style="font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px;">
                      KTS MARKETS
                    </div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 3px;">
                      High-Precision Automated Trading & Signals
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- MAIN CARD (CLEAN WHITE LIGHT THEME) -->
          <tr>
            <td style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">

              <!-- Gold Top Accent Bar -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td height="5" style="background: linear-gradient(90deg, #b45309 0%, #f59e0b 50%, #fbbf24 100%); line-height: 5px; font-size: 0;">&nbsp;</td>
                </tr>
              </table>

              <!-- Card Body Area -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="padding: 34px 30px;">

                    <!-- Greeting Heading -->
                    <h1 style="color: #0f172a; font-size: 23px; font-weight: 900; margin: 0 0 8px 0; line-height: 1.3;">
                      🎉 Welcome to KTS Markets!
                    </h1>
                    <p style="color: #475569; font-size: 14.5px; margin: 0 0 24px 0; line-height: 1.6;">
                      Hello <strong style="color: #0f172a;">{{ $user->name }}</strong>, your trading account has been successfully created and activated. You can now log in to the <strong>KTS Markets Mobile App</strong> and access real-time trading signals, automated bots, and community insights.
                    </p>

                    <!-- Account Credentials Box -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 26px;">
                      <tr>
                        <td style="padding: 20px 22px;">
                          <div style="font-size: 11.5px; font-weight: 800; color: #b45309; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">
                            🔐 YOUR LOGIN CREDENTIALS
                          </div>

                          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 14px;">
                            <tr>
                              <td style="padding: 6px 0; color: #64748b; font-weight: 600; width: 35%;">Registered Email:</td>
                              <td style="padding: 6px 0; color: #0f172a; font-weight: 700; word-break: break-all;">
                                {{ $user->email }}
                              </td>
                            </tr>
                            @if(!empty($password))
                            <tr>
                              <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Password:</td>
                              <td style="padding: 6px 0; color: #0f172a; font-family: monospace; font-size: 15px; font-weight: 800; letter-spacing: 0.5px;">
                                @if($password === 'Google Sign-In')
                                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px; font-family: inherit; font-size: 12.5px;">Google Authentication</span>
                                @else
                                <span style="background: #e2e8f0; padding: 2px 8px; border-radius: 6px;">{{ $password }}</span>
                                @endif
                              </td>
                            </tr>
                            @endif
                            <tr>
                              <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Account Status:</td>
                              <td style="padding: 6px 0;">
                                <span style="display: inline-block; background-color: #dcfce7; color: #15803d; font-size: 12px; font-weight: 800; padding: 2px 10px; border-radius: 12px; border: 1px solid #86efac;">
                                  ● ACTIVE &amp; VERIFIED
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>

                    <!-- What's Inside KTS Markets -->
                    <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                      ⚡ What You Get with KTS Markets:
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 26px;">
                      <tr>
                        <td style="padding: 8px 0; vertical-align: top; width: 24px;">📈</td>
                        <td style="padding: 8px 0 8px 8px; color: #334155; font-size: 13.5px; line-height: 1.5;">
                          <strong>10 Pips Scalping Signals:</strong> Instant entry, stop loss, and take profit targets with live market tracking.
                        </td>
                      </tr>
                      <tr>
                        <td style="padding: 8px 0; vertical-align: top; width: 24px;">🤖</td>
                        <td style="padding: 8px 0 8px 8px; color: #334155; font-size: 13.5px; line-height: 1.5;">
                          <strong>MT5 Bot Automation:</strong> Connect your Exness MT5 account and automate trades with our custom 10 Pips bot.
                        </td>
                      </tr>
                      <tr>
                        <td style="padding: 8px 0; vertical-align: top; width: 24px;">💬</td>
                        <td style="padding: 8px 0 8px 8px; color: #334155; font-size: 13.5px; line-height: 1.5;">
                          <strong>Community Chat:</strong> Chat with fellow traders, share analysis, and get live market updates.
                        </td>
                      </tr>
                      <tr>
                        <td style="padding: 8px 0; vertical-align: top; width: 24px;">🎓</td>
                        <td style="padding: 8px 0 8px 8px; color: #334155; font-size: 13.5px; line-height: 1.5;">
                          <strong>Education &amp; Video Lessons:</strong> Free professional forex and gold trading video courses.
                        </td>
                      </tr>
                    </table>

                    <!-- Direct WhatsApp Support Button -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                      <tr>
                        <td align="center">
                          <a href="https://wa.me/923371244640?text=Hello%20KTS%20Markets%20Team%2C%20I%20have%20logged%20in%20with%20{{ urlencode($user->email) }}" target="_blank" style="display: inline-block; background: #25D366; color: #ffffff; text-decoration: none; font-size: 14.5px; font-weight: 700; padding: 13px 26px; border-radius: 10px; box-shadow: 0 4px 14px rgba(37,211,102,0.35);">
                            💬 Connect on Official WhatsApp Support
                          </a>
                        </td>
                      </tr>
                    </table>

                    <p style="color: #64748b; font-size: 12.5px; line-height: 1.6; margin: 0; text-align: center;">
                      For security reasons, never share your account password with anyone. If you did not create this account, please contact our support team immediately.
                    </p>

                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td align="center" style="padding-top: 24px;">
              <p style="color: #94a3b8; font-size: 11.5px; line-height: 1.6; margin: 0;">
                &copy; {{ date('Y') }} KTS Markets. All rights reserved.<br>
                High-Precision Automated Trading Ecosystem.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
