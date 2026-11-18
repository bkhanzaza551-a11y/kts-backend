<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your MT5 Bot File is Ready - KTS Markets</title>
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
                    <div style="font-size: 20px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px;">
                      KTS MARKETS
                    </div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 3px;">
                      MT5 High-Precision Automated Trading System
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
                      🤖 Your MT5 Bot File is Ready!
                    </h1>
                    <p style="color: #475569; font-size: 14.5px; margin: 0 0 24px 0; line-height: 1.6;">
                      Hello <strong style="color: #0f172a;">{{ $user->name }}</strong>, your trading bot file has been approved by the admin. You can download and install it using the button below.
                    </p>

                    <!-- Admin Note (if any) -->
                    @if(!empty($adminNote))
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                      <tr>
                        <td style="background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 18px;">
                          <div style="font-size: 11.5px; font-weight: 800; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            📝 Note from Admin
                          </div>
                          <div style="font-size: 13.5px; color: #92400e; line-height: 1.55;">
                            {{ $adminNote }}
                          </div>
                        </td>
                      </tr>
                    </table>
                    @endif

                    <!-- ★ UPPER / MIDDLE SECTION: PROMINENT BOT FILE CARD & DIRECT DOWNLOAD BUTTON ★ -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(180deg, #fefce8 0%, #fef9c3 100%); border: 2px solid #fde047; border-radius: 14px; margin-bottom: 28px; box-shadow: 0 4px 14px rgba(234, 179, 8, 0.12);">
                      <tr>
                        <td style="padding: 22px 20px;">

                          <!-- File Metadata Row -->
                          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 18px;">
                            <tr>
                              <!-- Icon Column -->
                              <td width="52" align="center" style="vertical-align: middle;">
                                <table cellpadding="0" cellspacing="0" border="0">
                                  <tr>
                                    <td align="center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; font-size: 24px; line-height: 48px; color: #ffffff; box-shadow: 0 4px 10px rgba(217, 119, 6, 0.25);">
                                      ⚡
                                    </td>
                                  </tr>
                                </table>
                              </td>
                              <!-- Details Column -->
                              <td style="padding-left: 14px; vertical-align: middle;">
                                <div style="color: #92400e; font-weight: 900; font-size: 16px; margin-bottom: 3px; font-family: monospace, monospace;">
                                  {{ $bot->bot_file_name ?? 'KTS10PipsBot_v2.ex5' }}
                                </div>
                                <div style="color: #78350f; font-size: 12.5px; line-height: 1.4;">
                                  <strong>{{ $bot->name ?? 'KTS10 Pips Bot' }}</strong> &nbsp;•&nbsp;
                                  <span style="background-color: #fde68a; color: #92400e; padding: 2px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 800; letter-spacing: 0.5px;">{{ strtoupper($bot->bot_file_type ?? 'EX5') }} FILE</span>
                                  @if(!empty($bot->bot_file_size))
                                    &nbsp;•&nbsp; {{ $bot->bot_file_size }}
                                  @endif
                                </div>
                              </td>
                            </tr>
                          </table>

                          <!-- ATTACHMENT NOTICE (NO EXTERNAL LINKS - 100% SECURE) -->
                          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fef3c7; border: 1px dashed #f59e0b; border-radius: 10px;">
                            <tr>
                              <td align="center" style="padding: 14px 16px;">
                                <div style="font-size: 14px; font-weight: 800; color: #92400e; margin-bottom: 4px;">
                                  📎 File Is Email Ke Saath Attached Hai
                                </div>
                                <div style="font-size: 12px; color: #78350f; line-height: 1.5;">
                                  Email ke aakhir mein mojood <strong>{{ $bot->bot_file_name ?? 'KTS10PipsBot_v2.ex5' }}</strong> attachment box par click karke direct save karein.
                                </div>
                              </td>
                            </tr>
                          </table>

                        </td>
                      </tr>
                    </table>

                    <!-- Step-by-Step Installation Header -->
                    <div style="color: #0f172a; font-size: 16px; font-weight: 800; margin-bottom: 16px;">
                      📋 Step-by-Step Installation Guide
                    </div>

                    <!-- Steps Table (100% email client compatible) -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 26px;">
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                1
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          Click the <strong style="color: #b45309;">Download Bot File</strong> button above (or download from the email attachment at the bottom).
                        </td>
                      </tr>
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                2
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          Open <strong style="color: #0f172a;">MetaTrader 5 (MT5)</strong> on your Windows PC or VPS.
                        </td>
                      </tr>
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                3
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          In the top menu, go to <strong style="color: #0f172a;">File</strong> &rarr; <strong style="color: #0f172a;">Open Data Folder</strong>.
                        </td>
                      </tr>
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                4
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          Open the <strong style="color: #d97706;">MQL5</strong> folder &rarr; then open the <strong style="color: #d97706;">Experts</strong> folder.
                        </td>
                      </tr>
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                5
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          Paste the <strong style="color: #b45309; font-family: monospace;">{{ $bot->bot_file_name ?? 'KTS10PipsBot_v2.ex5' }}</strong> file directly inside the <strong style="color: #0f172a;">Experts</strong> folder.
                        </td>
                      </tr>
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                6
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          Restart MT5 (or in Navigator panel, right-click <strong style="color: #0f172a;">Expert Advisors</strong> &rarr; click <strong style="color: #0f172a;">Refresh</strong>).
                        </td>
                      </tr>
                      <tr>
                        <td width="36" align="left" style="vertical-align: top; padding-bottom: 12px;">
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" style="width: 24px; height: 24px; background-color: #f59e0b; color: #ffffff; font-weight: 800; font-size: 12px; line-height: 24px; border-radius: 50%; font-family: Arial, sans-serif;">
                                7
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td align="left" style="vertical-align: top; padding-top: 2px; padding-bottom: 12px; color: #334155; font-size: 13.5px; line-height: 1.55;">
                          Drag the bot onto your chart and ensure <strong style="color: #16a34a;">Algo Trading</strong> is enabled in the MT5 top toolbar.
                        </td>
                      </tr>
                    </table>

                    <!-- Security Alert Box -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                      <tr>
                        <td style="background-color: #fef2f2; border: 1px solid #fee2e2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 14px 16px;">
                          <div style="color: #991b1b; font-size: 12px; font-weight: 800; margin-bottom: 3px;">
                            🔒 Security & License Notice
                          </div>
                          <div style="color: #b91c1c; font-size: 12px; line-height: 1.5;">
                            This software file is compiled exclusively for your registered account. Do not share, forward, or redistribute this file to unauthorized parties.
                          </div>
                        </td>
                      </tr>
                    </table>

                    <!-- Need Help Section -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="border-top: 1px solid #e2e8f0; padding-top: 18px; color: #64748b; font-size: 12.5px; line-height: 1.55;">
                          Need assistance setting up? Contact our support team directly via the <strong style="color: #0f172a;">KTS Markets Mobile App</strong> &rarr; <em>Support</em> section.
                        </td>
                      </tr>
                    </table>

                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td align="center" style="padding-top: 24px;">
              <div style="color: #64748b; font-size: 12px; margin-bottom: 4px;">
                &copy; {{ date('Y') }} <strong>KTS Markets</strong>. All rights reserved.
              </div>
              <div style="color: #94a3b8; font-size: 11px;">
                This automated dispatch was initiated from the KTS Markets Admin Console.
              </div>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
