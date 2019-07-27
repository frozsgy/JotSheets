sortable('.js-sortable', {
    forcePlaceholderSize: true,
    placeholderClass: 'mb1 bg-navy border border-yellow',
    hoverClass: 'bg-maroon yellow',
    itemSerializer: function(item, container) {
        item.parent = '[parentNode]'
        item.node = '[Node]'
        //item.html = item.html.replace('<','&lt;')
        return item
    },
    containerSerializer: function(container) {
        container.node = '[Node]'
        return container
    }
})
document.querySelector('.js-serialize-button').addEventListener('click', function() {
    let serialized = sortable('.js-sortable', 'serialize')
    var te = JSON.stringify(serialized, null, ' ');
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('POST', 'add.php?p=8');
    xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
    xmlhttp.send('data=' + encodeURIComponent(te));
    document.getElementById("formnext").disabled = true;
    setTimeout(function() {
        window.location.href = "add.php?p=6";
    }, 2000);
})

sortable('.js-sortable-disabled', {
    forcePlaceholderSize: true,
    items: ':not(.disabled)',
    placeholderClass: 'border border-orange mb1'
});
sortable('.js-sortable-disabled-inner', {
    forcePlaceholderSize: true,
    items: ':not(.disabled)',
    placeholderClass: 'border border-maroon mb1'
});
