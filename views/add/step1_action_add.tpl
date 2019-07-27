<h3>Step 1: Select Your JotForm</h3>
<form action="{dest}?p=2" method="post">
    <p>
        Either enter the URL of your JotForm, or select from the dropdown menu.
    </p>
    <p>
        <select name="item_list" id="item_list" onchange="update_url()" class="input">
            <option value="">Select Form</option>
            {forms}
        </select>
        <input type="url" placeholder="Copy and paste the JotForm URL" size="30" name="form_url" id="form_url" required>
    </p>
