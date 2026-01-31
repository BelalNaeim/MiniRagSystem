git<?php

namespace App\Services\AI;

class PromptBuilder
{
    public function build(string $question, string $context): string
    {
        return <<<PROMPT
System: Answer using the provided context only. If the answer is not in the context, say you don't know.
Context:
{$context}
Question:
{$question}
PROMPT;
    }
}
