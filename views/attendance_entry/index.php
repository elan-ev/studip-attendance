<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anwesenheit QR-Code</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f2f1ed;
            color: #333333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .header-info {
            text-align: center;
            margin-bottom: 24px;
        }

        .header-info h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1a1a1a;
        }

        .header-info p {
            font-size: 14px;
            color: #666666;
        }

        .qr-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.03);
            width: 100%;
            max-width: 380px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 24px 24px 24px;
            position: relative;
        }

        .card-top-bar {
            width: calc(100% + 48px);
            height: 6px;
            background: linear-gradient(90deg, #8ba8be 0%, #2b3a4a 100%);
            margin-bottom: 20px;
        }

        .qr-container {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .qr-container svg {
            width: 100%;
            height: 100%;
        }

        .instruction-text {
            text-align: center;
            font-size: 13px;
            line-height: 1.4;
            color: #4a4a4a;
            margin-bottom: 16px;
        }

        .token-box {
            width: 100%;
            border: 1px solid #d0d5dd;
            border-radius: 10px;
            padding: 10px 16px;
            text-align: center;
            background-color: #ffffff;
        }

        .token-code {
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 12px;
            color: #111111;
            /* Passt den Abstand für das letzte Zeichen aus */
            margin-right: -12px; 
            font-family: inherit;
        }
    </style>
</head>
<body>

    <div class="header-info">
        <h1>Testveranstaltung A</h1>
        <p>Dienstag, 18.08.2026, 10:15 - 11:45 Uhr</p>
    </div>

    <div class="qr-card">
        <div class="card-top-bar"></div>

        <div class="qr-container">
            <!-- QR-Code Dummy SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 29" shape-rendering="crispEdges">
                <path fill="#ffffff" d="M0 0h29v29H0z"/>
                <!-- Corner 1 -->
                <path fill="#000000" d="M1 1h7v7H1zM2 2v5h5V2zM3 3h3v3H3z"/>
                <!-- Corner 2 -->
                <path fill="#000000" d="M21 1h7v7h-7zM22 2v5h5V2zM23 3h3v3h-3z"/>
                <!-- Corner 3 -->
                <path fill="#000000" d="M1 21h7v7H1zM2 22v5h5v-5zM3 23h3v3H3z"/>
                <!-- Random Matrix Data (Dummy-Muster) -->
                <path fill="#000000" d="
                    M10 1h2v1h-2z M13 1h1v3h-1z M15 1h2v1h-2z M18 1h1v2h-1z 
                    M9 2h1v2h-1z M11 3h2v1h-2z M14 3h1v1h-1z M17 2h3v1h-3z
                    M9 5h3v1h-3z M13 5h2v2h-2z M17 5h1v1h-1z M19 4h1v3h-1z
                    M1 9h1v2h-1z M3 10h1v1h-1z M5 9h3v1h-3z M10 8h1v3h-1z
                    M12 9h2v1h-2z M15 8h3v1h-3z M19 9h2v2h-2z M22 9h1v1h-1z
                    M25 9h3v1h-3z M2 12h2v1h-2z M5 12h1v2h-1z M8 12h1v1h-1z
                    M10 12h3v1h-3z M14 11h2v3h-2z M18 12h2v1h-2z M22 11h2v1h-2z
                    M25 11h1v2h-1z M27 12h1v1h-1z M1 14h3v1h-3z M6 14h2v2h-2z
                    M11 14h1v1h-1z M13 15h3v1h-3z M17 14h1v3h-1z M20 14h3v1h-3z
                    M25 14h2v1h-2z M2 16h2v1h-2z M5 17h1v1h-1z M9 16h2v2h-2z
                    M12 17h2v1h-2z M15 17h1v2h-1z M19 16h1v1h-1z M21 17h2v1h-2z
                    M24 16h4v1h-4z M1 18h1v2h-1z M4 19h2v1h-2z M7 18h1v3h-1z
                    M9 19h3v1h-3z M13 19h1v1h-1z M16 19h2v1h-2z M20 19h1v2h-1z
                    M22 19h3v1h-3z M26 18h2v2h-2z M10 21h2v1h-2z M13 21h1v2h-1z
                    M15 22h2v1h-2z M18 21h3v1h-3z M22 22h1v2h-1z M25 21h2v1h-2z
                    M9 23h1v3h-1z M11 24h3v1h-3z M15 24h1v2h-1z M17 23h2v2h-2z
                    M20 24h2v1h-2z M24 23h4v1h-4z M10 26h3v1h-3z M14 27h2v1h-2z
                    M18 26h1v2h-1z M20 27h3v1h-3z M25 26h1v2h-1z M27 27h1v1h-1z
                "/>
            </svg>
        </div>

        <p class="instruction-text">
            Scannen Sie diesen Code ein oder<br>
            geben Sie den Token in Stud.IP ein.
        </p>

        <div class="token-box">
            <span class="token-code">614235</span>
        </div>
    </div>

</body>
</html>