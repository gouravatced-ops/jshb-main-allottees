<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>Login | {{ config('panel.portal_name') ?? 'Jharkhand Housing Board' }}</title>
  <meta name="description" content="Jharkhand Housing Board - Official management login portal" />
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset(config('panel.faviconIcon')) }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/font/font.css') }}">
  <link rel="stylesheet" href="{{ asset('css/icons/all.css') }}">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="ref-login-body">
    
    <div class="ref-main-wrapper">
        <div class="ref-login-box">
            
            <div class="ref-header">
                <div class="ref-header-left">
                    <img src="{{ asset(config('panel.logo')) }}" alt="Logo" onerror="this.src='https://placehold.co/100x70/ffffff/881326?text=Logo'">
                    <div class="ref-header-titles">
                        <h2>{{ config('panel.organization_hindi') }}</h2>
                        <h1>{{ config('panel.organization') }}</h1>
                    </div>
                </div>
                <div class="ref-header-right">
                    @if(config('panel.govermentLogo'))
                        <img src="{{ asset(config('panel.govermentLogo')) }}" alt="State Government Logo" style="height: 60px; margin-right: 15px;" onerror="this.style.display='none'">
                    @endif
                    <div class="ref-portal-badge">
                        <i class="fa-solid fa-desktop"></i> Member Portal
                    </div>
                </div>
            </div>

            <div class="ref-content">
                <div class="ref-content-left">
                    <h3>आवास सेवाओं का नया डिजिटल द्वार। 22</h3>
                    <ul>
                        <li>पोर्टल में "लॉग-इन" करने हेतु कृपया आपका ईमेल/यूजरनेम एवं पासवर्ड प्रविष्ट करें।</li>
                        <li>पासवर्ड न होने की स्थिति में "Forgot password?" लिंक का प्रयोग करें।</li>
                        <li>आवंटी पोर्टल अंतर्गत आप भवन का आवंटन आदेश, लेजर, एवं स्वयं का प्रोफाइल भी अपडेट करने की सुविधा दी जा रही है।</li>
                        <li>आवंटी पोर्टल अंतर्गत किश्तों का भुगतान ऑनलाइन किये जाने हेतु सुविधा दी गयी है।</li>
                        <li>आवंटन पत्र (Allotment Letter) के लिए आवेदन एवं डाउनलोड कर सकते हैं।</li>
                        <li>एग्रीमेंट (Agreement) हेतु आवेदन, दस्तावेज़ अपलोड एवं डाउनलोड कर सकते हैं।</li>
                        <li>कब्ज़ा पत्र (Possession Letter) हेतु आवेदन कर सकते हैं।</li>
                        <li>आवेदन कर उसकी प्रगति (Application Status) देख सकते हैं।</li>
                        <li>आवश्यक दस्तावेज़ अपलोड एवं डाउनलोड कर सकते हैं।</li>
                        <li>बोर्ड द्वारा जारी नोटिस एवं सूचनाएँ प्राप्त कर सकते हैं।</li>
                    </ul>
                </div>
                
                <div class="ref-divider"></div>
                
                <div class="ref-content-right">
                    @if (session('error'))
                    <div style="color: #b91c1c; background: #fef2f2; padding: 10px; border: 1px solid #fecaca; margin-bottom: 15px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                    </div>
                    @endif
                    @if (session('success'))
                    <div style="color: #15803d; background: #f0fdf4; padding: 10px; border: 1px solid #bbf7d0; margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                    @endif

                    @php
                    $otpRequired = session('otp_required', false);
                    $emailValue = old('email', session('email', ''));
                    @endphp

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <input type="hidden" name="otp_stage" value="{{ $otpRequired ? 1 : 0 }}">

                        <div class="ref-input-group">
                            <label for="email">ईमेल/यूजरनेम डाले</label>
                            <input id="email" name="email" type="text" value="{{ $emailValue }}" placeholder="ईमेल/यूजरनेम डाले" required>
                        </div>

                        @if (! $otpRequired)
                        <div class="ref-input-group" style="position: relative;">
                            <label for="password">पासवर्ड डाले</label>
                            <input id="password" name="password" type="password" placeholder="पासवर्ड डाले" required style="padding-right: 2.5rem;">
                            <i id="togglePassword" class="fa-solid fa-eye-slash toggle-pwd" style="position: absolute; right: 10px; bottom: 12px; cursor: pointer; color: #64748b;"></i>
                        </div>

                        <div class="ref-input-group">
                            <label for="captcha">कैप्चा डाले</label>
                            <div class="ref-captcha-row" style="margin-bottom: 5px;">
                                <div class="ref-captcha-box" style="padding: 0; border: none; background: transparent;">
                                    <img src="{{ captcha_src('flat') }}" onclick="this.src='{{ captcha_src('flat') }}'+Math.random()" alt="captcha" id="captcha-img" style="cursor: pointer; height: 42px; border-radius: 0.5rem; border: 1px solid #cbd5e1;">
                                </div>
                                <button type="button" class="ref-captcha-refresh" onclick="document.getElementById('captcha-img').src='{{ captcha_src('flat') }}'+Math.random()" title="Refresh"><i class="fa-solid fa-arrows-rotate"></i></button>
                            </div>
                            <input id="captcha" name="captcha" type="text" placeholder="कैप्चा डाले" autocomplete="off" required>
                            @error('captcha')
                                <span style="color: #b91c1c; font-size: 0.85rem; margin-top: 5px; display: block;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                            @enderror
                        </div>
                        @endif

                        @if ($otpRequired)
                        <div class="ref-input-group">
                            <label for="otp_code">ओ.टी.पी. डाले</label>
                            <input id="otp_code" name="otp_code" type="text" placeholder="6-digit OTP" inputmode="numeric" pattern="\d*" required>
                        </div>
                        @endif

                        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.9rem;">
                            <label style="display: flex; align-items: center; gap: 5px;">
                            </label>
                            <a href="{{ route('password.request') }}" style="color: #1e3a8a; text-decoration: none; font-weight: bold;">Forgot password?</a>
                        </div>

                        <button type="submit" class="ref-login-btn" id="loginBtn">
                            {{ $otpRequired ? 'Verify & Login' : 'लॉग इन करें' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="maroon-footer">
        <div>Site is designed by <a href="https://www.computered.in/" target="_blank" style="text-decoration: none; color:white;">Computer Ed.</a> © {{ config('panel.organization') }}.</div>
        <div class="maroon-footer-social">
            Tech Partner
            <a href="https://www.computered.in/" target="_blank" rel="noopener noreferrer" class="partner-badge">
              <img src="{{ asset(config('panel.techpatrnterLogo')) }}" width="20px" alt="Computer Ed">
            </a> | 
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-youtube"></i>
            <i class="fa-brands fa-x-twitter"></i>
            <i class="fa-brands fa-instagram"></i>
        </div>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>