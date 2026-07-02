<?php
// Chatbot API configuration.
// 1) Set CHATBOT_PROVIDER = 'gemini' to use the Gemini API via Google Cloud/Vertex AI.
// 2) Put your Gemini bearer token (Google access token) into CHATBOT_GEMINI_API_KEY.
//    The token must have permission for Vertex AI generative models.
// 3) Set CHATBOT_GEMINI_MODEL to the Gemini model you want to use.
// 4) If you want to use OpenAI instead, switch CHATBOT_PROVIDER to 'openai' and fill the OpenAI settings.

define('CHATBOT_PROVIDER', 'gemini');
define('CHATBOT_GEMINI_API_KEY', 'AQ.Ab8RN6JQ4MU66qDDQXvxbsXHT7tqlR6l5SnS8mta2z5lFOJigw');
define('CHATBOT_GEMINI_MODEL', 'gemini-2.5-flash-lite');

define('CHATBOT_OPENAI_API_KEY', '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');
define('CHATBOT_OPENAI_MODEL', 'gpt-4o-mini');
?>