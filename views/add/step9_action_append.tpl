<div class="p1 col col-1">
    <h2 class="h4 mt1 navy">Columns</h2>
    <ul class="p2 border border-navy cols sortable list flex flex-column list-reset" id="cols">
    </ul>
</div>
<div class="p1 col col-4">
    <h2 class="h4 mt1 navy">Spreadsheet Fields</h2>
    <ul class="p2 border border-navy js-sortable-copy-target sortable list flex flex-column list-reset">
    </ul>
</div>
<div class="p1 col col-6">
    <h2 class="h4 mt1 navy">Form Fields</h2>
    <ul class="border border-navy p2 js-sortable-copy sortable list flex flex-column list-reset">
        {questions}
    </ul>
    <input type="button" value="Previous" onClick="window.history.back();">
    <input type="button" class="js-serialize-button" value="Next" id="formnext">
    <input type="hidden" name="token" id="token" value="{token}">
    <script type="text/javascript" src="./js/fieldcopy.js">
    </script>
</div>
</section>
