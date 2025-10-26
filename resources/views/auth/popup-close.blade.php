<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Auth Completed</title>
</head>
<body>
<script>
(function(){
    // نخبر النافذة الأم أن المصادقة تمت (أو فشلت)
    try {
        if (window.opener && !window.opener.closed) {
            window.opener.postMessage({ success: {{ $success ? 'true' : 'false' }}, redirect: "{{ $redirect ?? '' }}" }, '*');
        }
    } catch (e) {
        // ignore
    }
    // أغلق النافذة بعد ثوانٍ قليلة لتكون مرئية إن أردت
    setTimeout(function(){ window.close(); }, 500);
})();
</script>
</body>
</html>