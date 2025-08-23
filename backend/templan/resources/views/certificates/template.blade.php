<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Conclusão</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 297mm;
            height: 210mm;
            position: relative;
            overflow: hidden;
        }
        
        .certificate-container {
            width: 100%;
            height: 100%;
            position: relative;
            background: white;
            margin: 15mm;
            width: calc(100% - 30mm);
            height: calc(100% - 30mm);
            border: 8px solid #2c3e50;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.3);
        }
        
        .certificate-border {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 3px solid #3498db;
            border-radius: 15px;
        }
        
        .certificate-content {
            padding: 40px 60px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .header {
            margin-bottom: 20px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .institution-name {
            font-size: 24px;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .certificate-title {
            font-size: 36px;
            color: #2c3e50;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin: 20px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .certificate-text {
            font-size: 18px;
            color: #34495e;
            line-height: 1.6;
            margin: 30px 0;
        }
        
        .student-name {
            font-size: 32px;
            color: #2c3e50;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
            text-decoration-color: #3498db;
        }
        
        .course-name {
            font-size: 24px;
            color: #3498db;
            font-weight: bold;
            font-style: italic;
            margin: 15px 0;
        }
        
        .completion-details {
            font-size: 16px;
            color: #7f8c8d;
            margin: 20px 0;
        }
        
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
        }
        
        .signature-section {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            width: 200px;
            height: 2px;
            background: #2c3e50;
            margin: 0 auto 10px;
        }
        
        .signature-label {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: bold;
        }
        
        .certificate-info {
            text-align: right;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .verification-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #3498db;
        }
        
        .decorative-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: -1;
        }
        
        .decorative-corner {
            position: absolute;
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #3498db, #2980b9);
            opacity: 0.1;
        }
        
        .decorative-corner.top-left {
            top: 30px;
            left: 30px;
            border-radius: 0 0 100% 0;
        }
        
        .decorative-corner.top-right {
            top: 30px;
            right: 30px;
            border-radius: 0 0 0 100%;
        }
        
        .decorative-corner.bottom-left {
            bottom: 30px;
            left: 30px;
            border-radius: 0 100% 0 0;
        }
        
        .decorative-corner.bottom-right {
            bottom: 30px;
            right: 30px;
            border-radius: 100% 0 0 0;
        }
        
        .grade-badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 0 10px;
        }
        
        .hours-badge {
            display: inline-block;
            background: #f39c12;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-border"></div>
        
        <div class="decorative-elements">
            <div class="decorative-corner top-left"></div>
            <div class="decorative-corner top-right"></div>
            <div class="decorative-corner bottom-left"></div>
            <div class="decorative-corner bottom-right"></div>
        </div>
        
        <div class="certificate-content">
            <div class="header">
                <div class="logo">EAD</div>
                <div class="institution-name">OnHCP - Plataforma de Ensino</div>
            </div>
            
            <div class="main-content">
                <h1 class="certificate-title">Certificado de Conclusão</h1>
                
                <div class="certificate-text">
                    Certificamos que
                </div>
                
                <div class="student-name">{{ $user->name }}</div>
                
                <div class="certificate-text">
                    concluiu com êxito o curso
                </div>
                
                <div class="course-name">{{ $course->title }}</div>
                
                <div class="completion-details">
                    Concluído em {{ $issued_date }}
                    @if($certificate->final_grade)
                        <span class="grade-badge">Nota: {{ number_format($certificate->final_grade, 1) }}</span>
                    @endif
                    @if($certificate->completion_hours)
                        <span class="hours-badge">{{ $certificate->completion_hours }}h de duração</span>
                    @endif
                </div>
                
                @if($course->description)
                    <div class="certificate-text" style="font-size: 14px; margin-top: 20px;">
                        {{ Str::limit($course->description, 200) }}
                    </div>
                @endif
            </div>
            
            <div class="footer">
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <div class="signature-label">Coordenação Acadêmica</div>
                </div>
                
                <div class="certificate-info">
                    <div><strong>Certificado Nº:</strong> {{ $certificate->certificate_number }}</div>
                    <div><strong>Código de Verificação:</strong></div>
                    <div class="verification-code">{{ $certificate->verification_code }}</div>
                    <div style="margin-top: 10px; font-size: 10px;">
                        Verifique a autenticidade em:<br>
                        {{ $verification_url }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>