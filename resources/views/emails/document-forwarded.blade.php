<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Forwarded</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa; color: #333333; line-height: 1.6; }
        .email-wrapper { width: 100%; background-color: #f4f7fa; padding: 40px 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .email-header { background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); padding: 40px 30px; text-align: center; color: #ffffff; }
        .email-header h1 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: -0.5px; }
        .email-header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
        .email-body { padding: 40px 30px; }
        .greeting { font-size: 18px; font-weight: 600; color: #7c3aed; margin-bottom: 20px; }
        .message { font-size: 15px; line-height: 1.8; color: #555555; margin-bottom: 25px; }
        .document-details { background-color: #f5f3ff; border-left: 4px solid #7c3aed; padding: 20px; border-radius: 8px; margin: 25px 0; }
        .detail-row { display: table; width: 100%; padding: 10px 0; border-bottom: 1px solid #ddd6fe; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { display: table-cell; font-weight: 600; color: #374151; width: 140px; padding-right: 15px; vertical-align: top; }
        .detail-value { display: table-cell; color: #555555; vertical-align: top; }
        .forward-path { background-color: #ede9fe; border-radius: 8px; padding: 15px; margin: 20px 0; text-align: center; }
        .forward-path-text { color: #5b21b6; font-size: 14px; font-weight: 600; }
        .forward-arrow { color: #7c3aed; font-size: 20px; margin: 0 10px; }
        .notes-box { background-color: #ede9fe; border: 1px solid #ddd6fe; border-radius: 8px; padding: 15px; margin: 20px 0; }
        .notes-box strong { color: #5b21b6; display: block; margin-bottom: 8px; font-size: 14px; }
        .notes-box p { color: #4c1d95; margin: 0; font-size: 14px; line-height: 1.6; font-style: italic; }
        .info-badge { display: inline-block; background-color: #ede9fe; color: #7c3aed; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-header, .email-body { padding: 30px 20px; }
            .email-header h1 { font-size: 22px; }
            .detail-row { display: block; }
            .detail-label { display: block; margin-bottom: 5px; width: 100%; }
            .detail-value { display: block; width: 100%; }
            .forward-path-text { font-size: 12px; }
            .forward-arrow { font-size: 16px; margin: 0 5px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>Document Forwarded</h1>
                <p>Document Tracking System</p>
            </div>

            <div class="email-body">
                <div class="greeting">Hello {{ $user->name }}!</div>

                <div style="text-align: center;">
                    <span class="info-badge">New Document</span>
                </div>

                <div class="message">
                    A document has been forwarded to your unit for review and action.
                </div>

                <div class="forward-path">
                    <div class="forward-path-text">
                        {{ $forwardHistory->fromUnit->name }}
                        <span class="forward-arrow">-></span>
                        {{ $forwardHistory->toUnit->name }}
                    </div>
                </div>

                <div class="document-details">
                    <div class="detail-row">
                        <div class="detail-label">Document No:</div>
                        <div class="detail-value"><strong>{{ $document->document_number }}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Title:</div>
                        <div class="detail-value">{{ $document->title }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">From Unit:</div>
                        <div class="detail-value">{{ $forwardHistory->fromUnit->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">To Unit:</div>
                        <div class="detail-value">{{ $forwardHistory->toUnit->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Forwarded by:</div>
                        <div class="detail-value">{{ $forwardHistory->forwardedBy->name }}</div>
                    </div>
                </div>

                @if($forwardHistory->notes)
                    <div class="notes-box">
                        <strong>Forwarding Notes:</strong>
                        <p>{{ $forwardHistory->notes }}</p>
                    </div>
                @endif

                <div class="message">
                    Please review this document at your earliest convenience.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
