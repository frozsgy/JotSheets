function closeWindowShort()
{
    setTimeout(function() {
        window.close();
    }, 1500);
    window.opener.location.reload();
}
window.onload = closeWindowShort();
