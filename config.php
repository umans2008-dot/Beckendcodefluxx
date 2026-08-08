<?php
// Mengambil API Key dari Environment Variable Render (dengan fallback dari file Anda)[span_0](start_span)[span_0](end_span)
define('PAYDISINI_API_KEY', getenv('PAYDISINI_API_KEY') ?: 'sk_live_0ca3b9f60cb7316f9fb47f0b8391530a583293e4a93d3ea0');[span_1](start_span)[span_1](end_span)
define('PAYDISINI_SERVICE_QRIS', '11'); //[span_2](start_span)[span_2](end_span)
define('PAYDISINI_ENDPOINT', 'https://paydisini.co.id/api/'); //[span_3](start_span)[span_3](end_span)
