function closeWindow() {
    setTimeout(function() {
        window.close();
    }, 2500);
    window.opener.location.reload();
}

function update_url() {
    var e = document.getElementById("item_list");
    var id = e.options[e.selectedIndex].value;
    if (id == '') {
        document.getElementById('form_url').value = '';
    } else {
        document.getElementById('form_url').value = 'https://form.jotform.com/' + id;
    }
}

function openNew(url) {
    var ra=(new Date).getTime();
    var left=(screen.width/2)-500;
    var top=(screen.height/2)-300;
    window.open(url,'authenticateWindow'+ra,'height=600,width=1000,left='+left+',top='+top+',resizable=no,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=yes');
}
