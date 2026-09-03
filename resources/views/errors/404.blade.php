<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>404 | Page Not Found | {{ config('panel.portal_name') ?? 'Jharkhand State Housing Board' }}</title>
    <meta name="description" content="Page Not Found - 404" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset(config('panel.faviconIcon')) }}">
    <link rel="stylesheet" href="{{ asset('css/font/font.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons/all.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .error-code {
            font-size: 72px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
        }

        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }

        .error-desc {
            font-size: 15px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 25px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-direction: column;
        }

        .btn-return {
            background-color: #881326;
            /* JSHB Maroon */
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-return:hover {
            background-color: #6a0f1e;
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }
    </style>
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
                    <div class="ref-portal-badge" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                        <i class="fa-solid fa-map-location-dot"></i> Route Not Found
                    </div>
                </div>
            </div>

            <div class="ref-content">
                <div class="ref-content-left">
                    <h3 style="color: #ea580c;"><i class="fa-solid fa-circle-exclamation"></i> पृष्ठ नहीं मिला (Page Not Found)</h3>
                    <ul style="margin-top: 15px;">
                        <li>आप जिस पृष्ठ (Page) को खोजने का प्रयास कर रहे हैं वह उपलब्ध नहीं है।</li>
                        <li>संभवतः आपने गलत URL (Route) दर्ज किया है या वह पृष्ठ हटा दिया गया है।</li>
                        <li>कृपया सुनिश्चित करें कि आपने सही वेब पता (Address) टाइप किया है।</li>
                        <li>अपने सही डैशबोर्ड पर वापस जाने के लिए दाईं ओर दिए गए "Return to Dashboard" बटन का प्रयोग करें।</li>
                        <li>यदि यह समस्या बनी रहती है, तो कृपया सपोर्ट टीम से संपर्क करें।</li>
                    </ul>
                </div>

                <div class="ref-divider"></div>

                <div class="ref-content-right" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div class="error-code" style="color: #ea580c;">404</div>
                    <div class="error-title">Page Not Found</div>

                    <div class="error-desc" style="background: #fff7ed; border-color: #ffedd5;">
                        <i class="fa-solid fa-compass" style="font-size: 20px; display: block; margin-bottom: 10px; color: #ea580c;"></i>
                        The page or route you are looking for does not exist. Please check the URL or return to the dashboard.
                    </div>

                    <div class="action-buttons" style="width: 100%;">
                        <a href="{{ auth()->check() ? route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'dashboard') : route('login') }}" class="btn-return">
                            <i class="fa-solid fa-house"></i> Return to Dashboard
                        </a>
                        <a href="{{ route('login') }}" class="btn-secondary">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Go to Login Page
                        </a>
                    </div>
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

</body>

</html>
