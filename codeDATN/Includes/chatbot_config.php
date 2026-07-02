<?php
// Chatbot API configuration
// ⚠️ IMPORTANT: Replace API keys with your actual keys before deployment
// Don't expose these in public repositories!

define('CHATBOT_PROVIDER', 'gemini'); // Use 'gemini' or 'openai'
define('CHATBOT_GEMINI_API_KEY', 'AIzaSyDK'); // ← Update this with your actual Gemini API key from https://aistudio.google.com/apikey
define('CHATBOT_GEMINI_MODEL', 'gemini-1.5-flash');

// Fallback to OpenAI (optional)
define('CHATBOT_OPENAI_API_KEY', ''); // Leave empty if using Gemini
define('CHATBOT_OPENAI_MODEL', 'gpt-4o-mini');

// Chatbot settings
define('CHATBOT_MAX_CONTEXT_LENGTH', 2000); // Limit context size to reduce token usage
define('CHATBOT_MAX_STUDENTS_TO_FETCH', 10); // Max students in search results
define('CHATBOT_API_TIMEOUT', 30); // API timeout in seconds
?>
