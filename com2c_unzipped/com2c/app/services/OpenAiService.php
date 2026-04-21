<?php
class OpenAiService
{
    public function summarizeInquiry(array $payload): array
    {
        $cfg = config('ai');

        if (empty($cfg['api_key']) || $cfg['api_key'] === '여기에_실제_OPENAI_API_KEY') {
            throw new Exception('OpenAI API 키가 비어 있거나 placeholder 상태입니다.');
        }

        $visitorName = trim((string)($payload['visitor_name'] ?? ''));
        $visitorEmail = trim((string)($payload['visitor_email'] ?? ''));
        $inquiryType = trim((string)($payload['inquiry_type'] ?? 'general'));
        $message = trim((string)($payload['message'] ?? ''));

        $typeMap = [
            'general' => '일반 문의',
            'collab' => '협업 제안',
            'consulting' => '상담 요청',
            'recruiting' => '채용 제안',
            'booking' => '예약 문의',
        ];

        $typeLabel = $typeMap[$inquiryType] ?? '일반 문의';

        $developerPrompt = <<<PROMPT
너는 com2c의 문의 분류 비서다.
반드시 JSON만 출력해라.
키는 정확히 summary_text, fit_level, ai_recommendation 3개만 사용한다.

규칙:
1. summary_text는 한국어로 3~5줄 이내.
2. fit_level은 high, medium, low, unknown 중 하나만.
3. ai_recommendation은 한국어 1~2문장.
4. 입력에 근거해서만 판단.
5. JSON 외 다른 말 절대 금지.

예시:
{
  "summary_text": "문의자: 홍길동\\n문의 유형: 협업 제안\\n핵심 내용: 지역 프로젝트 협업 제안과 미팅 요청",
  "fit_level": "high",
  "ai_recommendation": "빠른 검토 후 미팅 가능 시간 확인을 권장합니다."
}
PROMPT;

        $userPrompt = <<<PROMPT
문의자: {$visitorName}
이메일: {$visitorEmail}
문의 유형: {$typeLabel}
문의 원문:
{$message}
PROMPT;

        $body = [
            'model' => $cfg['model'] ?? 'gpt-4o-mini',
            'input' => [
                [
                    'role' => 'developer',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $developerPrompt
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $userPrompt
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $cfg['api_key'],
            ],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('cURL 호출 실패: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('OpenAI HTTP 오류: ' . $httpCode . ' / ' . $result);
        }

        $decoded = json_decode($result, true);

        if (!is_array($decoded)) {
            throw new Exception('OpenAI 응답 JSON 디코딩 실패');
        }

        $text = '';

        if (!empty($decoded['output_text'])) {
            $text = trim((string)$decoded['output_text']);
        }

        if ($text === '' && !empty($decoded['output'][0]['content'][0]['text'])) {
            $text = trim((string)$decoded['output'][0]['content'][0]['text']);
        }

        if ($text === '') {
            throw new Exception('OpenAI 응답 텍스트가 비어 있습니다. raw=' . json_encode($decoded, JSON_UNESCAPED_UNICODE));
        }

        $json = json_decode($text, true);

        if (!is_array($json)) {
            throw new Exception('OpenAI JSON 파싱 실패: ' . $text);
        }

        $fit = $json['fit_level'] ?? 'unknown';
        $allowedFits = ['high', 'medium', 'low', 'unknown'];
        if (!in_array($fit, $allowedFits, true)) {
            $fit = 'unknown';
        }

        $summaryText = trim((string)($json['summary_text'] ?? ''));
        $recommendation = trim((string)($json['ai_recommendation'] ?? ''));

        if ($summaryText === '') {
            throw new Exception('summary_text가 비어 있습니다.');
        }

        return [
            'summary_text' => $summaryText,
            'fit_level' => $fit,
            'ai_recommendation' => $recommendation !== '' ? $recommendation : '추가 검토가 필요합니다.',
        ];
    }
}