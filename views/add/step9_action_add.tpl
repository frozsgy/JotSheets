<div class="col col-1">
    <ul class="ml1 js-sortable-disabled sortable list flex flex-column list-reset">
        {columns}
    </ul>
</div>
<div class="p6 clearfix silver">
    <div class="col col-6">
        <ul class="ml1 js-sortable sortable list flex flex-column list-reset">
            {questions}
        </ul>
        <input type="button" value="Previous" onClick="window.history.back();">
        <input type="button" class="js-serialize-button" value="Next" id="formnext">
        <script type="text/javascript" src="./js/fieldsort.js">
        </script>
    </div>
</div>
</section>
