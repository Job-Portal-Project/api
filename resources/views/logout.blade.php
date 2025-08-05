<!DOCTYPE html>
<html>
<head>
    <title>Signing out...</title>
</head>
<body>
<script>
    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    window.location = "{{ url('/') }}";
</script>
</body>
</html>
