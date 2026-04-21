<?php
class InquirySummaryService
{
    public function summarize(array $payload): array
    {
        try {
            $openAi = new OpenAiService();
            $result = $openAi->summarizeInquiry($payload);

            if (!empty($result['summary_text']) && !empty($result['fit_level'])) {
                $result['summary_text'] = "[OPENAI]\n" . $result['summary_text'];
                return $result;
            }

            return [
                'summary_text' => '[FALLBACK] OpenAI 결과가 비어 있습니다.',
                'fit_level' => 'unknown',
                'ai_recommendation' => 'OpenAI 응답이 비어 있어 fallback 처리되었습니다.',
            ];
        } catch (Throwable $e) {
            return [
                'summary_text' => '[FALLBACK] ' . $e->getMessage(),
                'fit_level' => 'unknown',
                'ai_recommendation' => 'OpenAI 호출 실패로 fallback 처리되었습니다.',
            ];
        }
    }
}