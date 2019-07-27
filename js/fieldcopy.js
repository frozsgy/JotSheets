sortable('.js-sortable-copy', {
    forcePlaceholderSize: true,
    copy: true,
    acceptFrom: false,
    placeholderClass: 'mb1 bg-navy border border-yellow',
});
sortable('.js-sortable-copy-target', {
    forcePlaceholderSize: true,
    acceptFrom: '.js-sortable-copy,.js-sortable-copy-target',
    placeholderClass: 'mb1 border border-maroon',
    itemSerializer: function(item, container) {
        item.parent = '[parentNode]'
        item.node = '[Node]'
        item.html = item.html.replace('<', '&lt;')
        return item
    },
    containerSerializer: function(container) {
        container.node = '[Node]'
        return container
    }
});
document.querySelector('.js-serialize-button').addEventListener('click', function() {
    let serialized = sortable('.js-sortable-copy-target', 'serialize')
    var te = JSON.stringify(serialized, null, ' ');
    var token = document.getElementById('token').value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('POST', 'append.php?p=8&id=' + token);
    xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
    xmlhttp.send('data=' + encodeURIComponent(te));
    document.getElementById("formnext").disabled = true;
    setTimeout(function() {
        window.location.href = "append.php?p=6&id=" + token;
    }, 2000);
});
document.querySelector('.js-sortable-copy-target').addEventListener("drop", function(event) {
    var ul = document.getElementById("cols");
    var tt = sortable('.js-sortable-copy-target', 'serialize');
    var children = ul.children.length + 1
    if (tt[0]['items'].length == (ul.children.length + 1)) {
        var li = document.createElement("li");
        li.setAttribute("id", "element" + children)
        li.setAttribute("class", "p1 mb1 navy bg-teal disabled");
        li.setAttribute("style", "position: relative; z-index: 10");
        li.appendChild(document.createTextNode(toColumnName(tt[0]['items'].length)));
        ul.appendChild(li);
    }
}, false);

function toColumnName(num) {
    for (var ret = '', a = 1, b = 26;
        (num -= a) >= 0; a = b, b *= 26) {
        ret = String.fromCharCode(parseInt((num % b) / a) + 65) + ret;
    }
    return ret;
}
