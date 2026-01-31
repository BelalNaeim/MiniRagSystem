<?php

namespace App\Services\AI;

class PromptBuilder
{
    public function build(string $question, string $context): string
    {
        return <<<PROMPT
You are a helpful assistant. Answer the question based ONLY on the following context.
If the answer cannot be found in the context, say "I don't have enough information to answer this question."

Context:
{$context}

Question: {$question}

Answer:
PROMPT;
    }
}
