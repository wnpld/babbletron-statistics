function startManualForm() {
    // Changes form values for annual and static entries
    // Sets section information
    // Hides options form and shows main form
    const sectionfield = document.getElementById("sectionselect");
    sectionid = sectionfield.ariaSelected;
    sectionname = sectionfield.options[sectionfield.selectedIndex].text;
    document.getElementById("catheading").textContent = sectionname;
    document.getElementById("catid").value = sectionid;
    if (document.getElementById("freqradioannual").ariaChecked) {
        document.getElementById("month").remove;
        document.getElementById("yearname").setAttribute("name","fieldname0");
        document.getElementById("yeartype").setAttribute("name","yeartype0");
        document.getElementById("fieldcount").value = 1;
    } else if (document.getElementById("freqradiostatic").ariaChecked) {
        document.getElementById("month").remove;
        document.getElementById("year").remove;
        document.getElementById("fieldcount").value = 0;
    }
    // default is monthly
    document.getElementById("dsourceoptions").setAttribute("style", "display:none;");
    document.getElementById("dsourceform").setAttribute("style", "display:block;");
}

function addFormField(formtype) {
    const fieldlist = document.getElementById("dsourcelist");
    var fieldcount = document.getElementById("fieldcount").value;
    const newcard = document.createElement("div");
    newcard.classList.add("card");
    newcard.classList.add("mt-3");
    newcard.classList.add("mr-3");
    newcard.classList.add("ml-3");
    newcard.setAttribute("id", "row" + fieldcount.toString());
    const newcardbody = document.createElement("div");
    newcardbody.classList.add("card-body");
    newcard.appendChild(newcardbody);
    const newrow = document.createElement("div");
    newrow.classList.add("row");
    newcardbody.appendChild(newrow);

    // Name Field
    const namecol = document.createElement("div");
    namecol.classList.add("col");
    namecol.classList.add("form-group");
    newrow.appendChild(namecol);
    const namelabel = document.createElement("label");
    namelabel.classList.add("form-label");
    namelabel.setAttribute("for", "fieldname" + fieldcount.toString());
    namelabel.setAttribute("id", "namelabel" + fieldcount.toString());
    namelabel.setAttribute("required", "");
    namelabel.textContent = "Field Name";
    namecol.appendChild(namelabel);
    const namefield = document.createElement("input");
    namefield.setAttribute("type", "text")
    namefield.setAttribute("id", "fieldname" + fieldcount.toString());
    namefield.setAttribute("name", "fieldname" + fieldcount.toString());
    namefield.setAttribute("size", "50");
    namecol.appendChild(namefield);

    // Type Field
    const typecol = document.createElement("div");
    typecol.classList.add("col");
    typecol.classList.add("form-group");
    newrow.appendChild(typecol);
    const typelabel = document.createElement("label");
    typelabel.classList.add("form-label");
    typelabel.setAttribute("for", "fieldtype" + fieldcount.toString());
    typelabel.setAttribute("id", "typelabel" + fieldcount.toString());
    typelabel.textContent = "Field Type";
    typecol.appendChild(typelabel);
    const typeselect = document.createElement("select");
    typeselect.classList.add("form-select");
    typeselect.setAttribute("id", "fieldtype" + fieldcount.toString());
    typeselect.setAttribute("name", "fieldtype" + fieldcount.toString());
    typeselect.setAttribute("onchange", "changeOptions(" + fieldcount.toString() + ")");
    typeselect.setAttribute("aria-describedby", "typehelp" + fieldcount.toString());
    const typeoptiontext = document.createElement("option");
    typeoptiontext.text = "Text";
    typeoptiontext.value = "text";
    typeselect.appendChild(typeoptiontext);
    const typeoptionnumber = document.createElement("option");
    typeoptionnumber.text = "Number";
    typeoptionnumber.value = "number";
    typeoptionnumber.setAttribute("selected", "");
    typeselect.appendChild(typeoptionnumber);
    const typeoptioncurrency = document.createElement("option");
    typeoptioncurrency.text = "Currency";
    typeoptioncurrency.value = "currency";
    typeselect.appendChild(typeoptioncurrency);
    const typeoptionlistadjust = document.createElement("option");
    typeoptionlistadjust.text = "List (Adjustable)";
    typeoptionlistadjust.value = "adjustlist";
    typeselect.appendChild(typeoptionlistadjust);
    const typeoptionlistfixed = document.createElement("option");
    typeoptionlistfixed.text = "List (Fixed)";
    typeoptionlistfixed.value = "fixedlist";
    typeselect.appendChild(typeoptionlistfixed);
    const typeoptiondate = document.createElement("option");
    typeoptiondate.text = "Date";
    typeoptiondate.value = "date";
    typeselect.appendChild(typeoptiondate);
    typecol.appendChild(typeselect);
    const typehelp = document.createElement("div");
    typehelp.classList.add("form-text");
    typehelp.setAttribute("id", "typehelp" + fieldcount.toString());
    typehelp.textContent = "Choose a maximum range for this number.";
    typecol.appendChild(typehelp);

    // Option Field
    // Options are highly variable and mainly setup by a different
    // script when a field type is selected.  The default setup
    // is for a number
    const optioncol = document.createElement("div");
    optioncol.setAttribute("id", "options" + fieldcount.toString());
    optioncol.classList.add("col");
    optioncol.classList.add("form-group");
    const optionblock = document.createElement("div");
    optionblock.setAttribute("id", "optionset" + fieldcount.toString());
    const fieldoptions = document.createElement("input");
    fieldoptions.setAttribute("type", "hidden");
    fieldoptions.setAttribute("id", "fieldoptions" + fieldcount.toString());
    fieldoptions.setAttribute("name", "fieldoptions" + fieldcount.toString());
    fieldoptions.value = "smallint";
    optionblock.appendChild(fieldoptions);
    const tinyintdiv = document.createElement("div");
    tinyintdiv.classList.add("form-check");
    const tinyintcheck = document.createElement("input");
    tinyintcheck.classList.add("form-check-input");
    tinyintcheck.setAttribute("type", "radio");
    tinyintcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    tinyintcheck.setAttribute("id", "tinyint" + fieldcount.toString());
    tinyintcheck.setAttribute("onclick", "updateFieldOptions('tinyint','" + fieldcount.toString() + "')");
    const tinyintlabel = document.createElement("label");
    tinyintlabel.classList.add("form-check-label");
    tinyintlabel.setAttribute("for", "tinyint" + fieldcount.toString());
    tinyintlabel.setAttribute("id", "tinyintlabel" + fieldcount.toString());
    tinyintlabel.textContent = "Less than 200";
    tinyintdiv.appendChild(tinyintcheck);
    tinyintdiv.appendChild(tinyintlabel);
	optionblock.appendChild(tinyintdiv);
    const smallintdiv = document.createElement("div");
    smallintdiv.classList.add("form-check");
    const smallintcheck = document.createElement("input");
    smallintcheck.classList.add("form-check-input");
    smallintcheck.setAttribute("type", "radio");
    smallintcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    smallintcheck.setAttribute("id", "smallint" + fieldcount.toString());
    smallintcheck.setAttribute("onclick", "updateFieldOptions('smallint','" + fieldcount.toString() + "')");
    smallintcheck.setAttribute("checked", "");
    const smallintlabel = document.createElement("label");
    smallintlabel.classList.add("form-check-label");
    smallintlabel.setAttribute("for", "smallint" + fieldcount.toString());
    smallintlabel.setAttribute("id", "smallintlabel" + fieldcount.toString());
    smallintlabel.textContent = "Less than 50,000";
    smallintdiv.appendChild(smallintcheck);
    smallintdiv.appendChild(smallintlabel);
    optionblock.appendChild(smallintdiv);
    const intdiv = document.createElement("div");
    intdiv.classList.add("form-check");
    const intcheck = document.createElement("input");
    intcheck.classList.add("form-check-input");
    intcheck.setAttribute("type", "radio");
    intcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    intcheck.setAttribute("id", "int" + fieldcount.toString());
    intcheck.setAttribute("onclick", "updateFieldOptions('int','" + fieldcount.toString() + "')");
    const intlabel = document.createElement("label");
    intlabel.classList.add("form-check-label");
    intlabel.setAttribute("for", "int" + fieldcount.toString());
    intlabel.setAttribute("id", "intlabel" + fieldcount.toString());
    intlabel.textContent = "Less than 4,000,000";
    intdiv.appendChild(intcheck);
    intdiv.appendChild(intlabel);
    optionblock.appendChild(intdiv);
    const bigintdiv = document.createElement("div");
    bigintdiv.classList.add("form-check");
    const bigintcheck = document.createElement("input");        
    bigintcheck.classList.add("form-check-input");
    bigintcheck.setAttribute("type", "radio");
    bigintcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    bigintcheck.setAttribute("id", "bigint" + fieldcount.toString());
    bigintcheck.setAttribute("onclick", "updateFieldOptions('bigint','" + fieldcount.toString() + "')");
    const bigintlabel = document.createElement("label");
    bigintlabel.classList.add("form-check-label");
    bigintlabel.setAttribute("for", "bigint" + fieldcount.toString());
    bigintlabel.setAttribute("id", "bigintlabel" + fieldcount.toString());
    bigintlabel.textContent = "More than 4,000,000";
    bigintdiv.appendChild(bigintcheck);
    bigintdiv.appendChild(bigintlabel);
    optionblock.appendChild(bigintdiv);
    optioncol.appendChild(optionblock);
    newrow.appendChild(optioncol);
    fieldlist.appendChild(newcard);

    // Delete Column
    // Has a button that can be used to delete the row
    const deletecol = document.createElement("div");
    deletecol.classList.add("col");
    deletecol.classList.add("form-group");
    newrow.appendChild(deletecol);
    const deletebutton = document.createElement("input");
    deletebutton.id = "delete" + fieldcount.toString();
    deletebutton.classList.add("btn");
    deletebutton.classList.add("btn-danger");
    deletebutton.setAttribute("type", "button");
    deletebutton.setAttribute("onclick", "deleteRow(" + fieldcount.toString() + ")");
    deletebutton.value = "Delete";
    deletecol.appendChild(deletebutton);
    
    fieldcount++;
    document.getElementById("fieldcount").value = fieldcount;
}

function changeOptions(fieldcount) {
    const typeselect = document.getElementById("fieldtype" + fieldcount.toString());
    const datatype = typeselect.value;

    // delete the current option set and then put a new one in
    const oldblock = document.getElementById("optionset" + fieldcount.toString());
    oldblock.remove();

    const newblock = document.createElement("div");
    newblock.setAttribute("id", "optionset" + fieldcount.toString());
    document.getElementById("options" + fieldcount.toString()).appendChild(newblock);

    if (datatype == "number") {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Choose a maximum range for this number.";
    	const fieldoptions = document.createElement("input");
    	fieldoptions.setAttribute("type", "hidden");
    	fieldoptions.setAttribute("id", "fieldoptions" + fieldcount.toString());
    	fieldoptions.setAttribute("name", "fieldoptions" + fieldcount.toString());
    	fieldoptions.value = "smallint";
    	newblock.appendChild(fieldoptions);
    	const tinyintdiv = document.createElement("div");
    	tinyintdiv.classList.add("form-check");
    	const tinyintcheck = document.createElement("input");
    	tinyintcheck.classList.add("form-check-input");
    	tinyintcheck.setAttribute("type", "radio");
    	tinyintcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    	tinyintcheck.setAttribute("id", "tinyint" + fieldcount.toString());
    	tinyintcheck.setAttribute("onclick", "updateFieldOptions('tinyint','" + fieldcount.toString() + "')");
    	const tinyintlabel = document.createElement("label");
    	tinyintlabel.classList.add("form-check-label");
    	tinyintlabel.setAttribute("for", "tinyint" + fieldcount.toString());
        tinyintlabel.setAttribute("id", "tinyintlabel" + fieldcount.toString());
    	tinyintlabel.textContent = "Less than 200";
    	tinyintdiv.appendChild(tinyintcheck);
    	tinyintdiv.appendChild(tinyintlabel);
		newblock.appendChild(tinyintdiv);
    	const smallintdiv = document.createElement("div");
    	smallintdiv.classList.add("form-check");
    	const smallintcheck = document.createElement("input");
    	smallintcheck.classList.add("form-check-input");
    	smallintcheck.setAttribute("type", "radio");
    	smallintcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    	smallintcheck.setAttribute("id", "smallint" + fieldcount.toString());
    	smallintcheck.setAttribute("onclick", "updateFieldOptions('smallint','" + fieldcount.toString() + "')");
    	smallintcheck.setAttribute("checked", "");
    	const smallintlabel = document.createElement("label");
    	smallintlabel.classList.add("form-check-label");
    	smallintlabel.setAttribute("for", "smallint" + fieldcount.toString());
        smallintlabel.setAttribute("id", "smallintlabel" + fieldcount.toString());
    	smallintlabel.textContent = "Less than 50,000";
    	smallintdiv.appendChild(smallintcheck);
    	smallintdiv.appendChild(smallintlabel);
    	newblock.appendChild(smallintdiv);
    	const intdiv = document.createElement("div");
    	intdiv.classList.add("form-check");
    	const intcheck = document.createElement("input");
    	intcheck.classList.add("form-check-input");
    	intcheck.setAttribute("type", "radio");
    	intcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    	intcheck.setAttribute("id", "int" + fieldcount.toString());
    	intcheck.setAttribute("onclick", "updateFieldOptions('int','" + fieldcount.toString() + "')");
    	const intlabel = document.createElement("label");
    	intlabel.classList.add("form-check-label");
    	intlabel.setAttribute("for", "int" + fieldcount.toString());
        intlabel.setAttribute("id", "intlabel" + fieldcount.toString());  
    	intlabel.textContent = "Less than 4,000,000";
    	intdiv.appendChild(intcheck);
    	intdiv.appendChild(intlabel);
    	newblock.appendChild(intdiv);
    	const bigintdiv = document.createElement("div");
    	bigintdiv.classList.add("form-check");
    	const bigintcheck = document.createElement("input");        
    	bigintcheck.classList.add("form-check-input");
    	bigintcheck.setAttribute("type", "radio");
    	bigintcheck.setAttribute("name", "numbertype" + fieldcount.toString());
    	bigintcheck.setAttribute("id", "bigint" + fieldcount.toString());
    	bigintcheck.setAttribute("onclick", "updateFieldOptions('bigint','" + fieldcount.toString() + "')");
    	const bigintlabel = document.createElement("label");
    	bigintlabel.classList.add("form-check-label");
    	bigintlabel.setAttribute("for", "bigint" + fieldcount.toString());
        bigintlabel.setAttribute("id", "bigintlabel" + fieldcount.toString()); 
    	bigintlabel.textContent = "More than 4,000,000";
    	bigintdiv.appendChild(bigintcheck);
    	bigintdiv.appendChild(bigintlabel);
        newblock.appendChild(bigintdiv);
        
    } else if (datatype == "currency") {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Choose a maximum amount";
        const fieldoptions = document.createElement("input");
        fieldoptions.setAttribute("type", "hidden");
        fieldoptions.setAttribute("id", "fieldoptions" + fieldcount.toString());
        fieldoptions.setAttribute("name", "fieldoptions" + fieldcount.toString());
        fieldoptions.value = "8";
        newblock.appendChild(fieldoptions);
        const lowvaluediv = document.createElement("div");
        lowvaluediv.classList.add("form-check");
        const lowvaluecheck = document.createElement("input");
        lowvaluecheck.classList.add("form-check-input");
        lowvaluecheck.setAttribute("type", "radio");
    	lowvaluecheck.setAttribute("name", "currencytype" + fieldcount.toString());
    	lowvaluecheck.setAttribute("id", "lowvalue" + fieldcount.toString());
        lowvaluecheck.setAttribute("onclick", "updateFieldOptions('5','" + fieldcount.toString() + "')");
    	const lowvaluelabel = document.createElement("label");
    	lowvaluelabel.classList.add("form-check-label");
    	lowvaluelabel.setAttribute("for", "lowvalue" + fieldcount.toString());
        lowvaluelabel.setAttribute("id", "lowvaluelabel" + fieldcount.toString());
        lowvaluelabel.textContent = "Up to $999"; 
        lowvaluediv.appendChild(lowvaluecheck);
        lowvaluediv.appendChild(lowvaluelabel);
        newblock.appendChild(lowvaluediv);
        const middlevaluediv = document.createElement("div");
        middlevaluediv.classList.add("form-check");        
        const middlevaluecheck = document.createElement("input");
        middlevaluecheck.classList.add("form-check-input");
        middlevaluecheck.setAttribute("type", "radio");
    	middlevaluecheck.setAttribute("name", "currencytype" + fieldcount.toString());
    	middlevaluecheck.setAttribute("id", "middlevalue" + fieldcount.toString());
        middlevaluecheck.setAttribute("onclick", "updateFieldOptions('8','" + fieldcount.toString() + "')");
    	const middlevaluelabel = document.createElement("label");
    	middlevaluelabel.classList.add("form-check-label");
    	middlevaluelabel.setAttribute("for", "middlevalue" + fieldcount.toString());
        middlevaluelabel.setAttribute("id", "middlevaluelabel" + fieldcount.toString());
        middlevaluelabel.textContent = "Up to $999,999";
        middlevaluecheck.setAttribute("checked", "");
        middlevaluediv.appendChild(middlevaluecheck);
        middlevaluediv.appendChild(middlevaluelabel);
        newblock.appendChild(middlevaluediv);
        const highvaluediv = document.createElement("div");
        highvaluediv.classList.add("form-check");          
        const highvaluecheck = document.createElement("input");
        highvaluecheck.classList.add("form-check-input");
        highvaluecheck.setAttribute("type", "radio");
    	highvaluecheck.setAttribute("name", "currencytype" + fieldcount.toString());
    	highvaluecheck.setAttribute("id", "highvalue" + fieldcount.toString());
        highvaluecheck.setAttribute("onclick", "updateFieldOptions('11','" + fieldcount.toString() + "')");
    	const highvaluelabel = document.createElement("label");
    	highvaluelabel.classList.add("form-check-label");
    	highvaluelabel.setAttribute("for", "highvalue" + fieldcount.toString());
        highvaluelabel.setAttribute("id", "highvaluelabel" + fieldcount.toString());
        highvaluelabel.textContent = "up to $999,999,999";
        highvaluediv.appendChild(highvaluecheck);
        highvaluediv.appendChild(highvaluelabel);
        newblock.appendChild(highvaluediv);

    } else if (datatype == "fixedlist" ) {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Use a fixed list if you expect this list to never change.  Months of the year, days of the week, and hours are common examples of this.";
        const optionlist = document.createElement("textarea");
        optionlist.setAttribute("name", "fieldoptions" + fieldcount.toString());
        optionlist.setAttribute("aria-describedby", "optionguide" + fieldcount.toString());
        newblock.appendChild(optionlist);
        const optionguide = document.createElement("div");
        optionguide.classList.add("form-text");
        optionguide.setAttribute("id", "optionguide" + fieldcount.toString());
        optionguide.textContent = "Enter a list of options separated by commas. Be careful of what you put here since you can't change it later."
        newblock.appendChild(optionguide);
    } else if (datatype == "adjustlist" ) {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Use an adjustable list if you expect this list to change over time.  Material types and media formats should go here.  If you need a list and aren't sure, use this one.";
        const optionlist = document.createElement("textarea");
        optionlist.setAttribute("name", "fieldoptions" + fieldcount.toString());
        optionlist.setAttribute("aria-describedby", "optionguide" + fieldcount.toString());
        newblock.appendChild(optionlist);
        const optionguide = document.createElement("div");
        optionguide.classList.add("form-text");
        optionguide.setAttribute("id", "optionguide" + fieldcount.toString());
        optionguide.textContent = "Enter a list of options separated by commas."
        newblock.appendChild(optionguide);
    } else if (datatype == "date") {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Dates are available as a type for situations where you're doing a static inventory.  Don't use this for date-based statistics.";
    } else {
        // text
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Select a maximum length for your text field.";
        const optionlabel = document.createElement("label");
        optionlabel.setAttribute("for", "fieldoptions" + fieldcount.toString());
        optionlabel.setAttribute("id", "optionlabel" + fieldcount.toString());
        optionlabel.classList.add("form-label");
        optionlabel.textContent = "Maximum text length in characters";
        newblock.appendChild(optionlabel);
        const optionsize = document.createElement("input");
        optionsize.classList.add("form-control");
        optionsize.setAttribute("type", "number");
        optionsize.setAttribute("id", "fieldoptions" + fieldcount.toString());
        optionsize.setAttribute("name", "fieldoptions" + fieldcount.toString());
        optionsize.setAttribute("min", "1");
        optionsize.setAttribute("max", "200");
        optionsize.value = "50";
        newblock.appendChild(optionsize);
    }
}

function updateFieldOptions(valueRange, fieldcount) {
    const options = document.getElementById('fieldoptions' + fieldcount);
    options.value = valueRange;
}

function deleteRow(fieldcount) {
    const deadrow = document.getElementById('row' + fieldcount);
    if (deadrow) {
        deadrow.remove();
    }
    //Adjust row counter and numbering of remaining rows, as necessary
    const rowcount = document.getElementById('fieldcount');
    var rowtotal = rowcount.value;
    rowtotal--;
    fieldcount.value = rowtotal;
    //Total value in fieldcount (the count of rows) should be greater than the
    //current row counter (counter starts from 0, so when you've got 2 rows, the
    //last row is 1).  If the row deleted is more than the new counter value
    //(e.g. it wasn't the last row), renumber the rows after it and their elements.
    if (rowtotal > fieldcount) {
        var currentrow = fieldcount + 1;
        var newrow = fieldcount;
        while (currentrow <= rowtotal) {
            const changerow = document.getElementById('row' + currentrow.toString());
            changerow.id = "row" + newrow.toString();
            //Field Name
            const changenamelabel = document.getElementById('namelabel' + currentrow.toString());
            changenamelabel.id = "namelabel" + newrow.toString();
            changenamelabel.setAttribute("for", "fieldname" + newrow.toString());
            const changename = document.getElementById('fieldname' + currentrow.toString());
            changename.id = "fieldname" + newrow.toString();
            changename.name = "fieldname" + newrow.toString();

            //Field Type
            const typelabel = document.getElementById('typelabel' + currentrow.toString());
            typelabel.id = "typelabel" + newrow.toString();
            typelabel.setAttribute("for", "fieldtype" + newrow.toString());
            const fieldtype = document.getElementById('fieldtype' + currentrow.toString());
            fieldtype.id = "fieldtype" + newrow.toString();
            fieldtype.name = "fieldtype" + newrow.toString();
            fieldtype.setAttribute("onChange", "changeOptions(" + newrow.toString() + ")");
            fieldtype.setAttribute("aria-describedBy", "typehelp" + newrow.toString());
            var chosentype = fieldtype.value;
            const typehelp = document.getElementById('typehelp' + currentrow.toString());
            typehelp.id = "typehelp" + newrow.toString();

            //Field Options
            const options = document.getElementById('options' + currentrow.toString());
            options.id = "options" + newrow.toString();
            const optionset = document.getElementById('optionset' + currentrow.toString());
            optionset.id = "optionset" + newrow.toString();
            const fieldoptions = document.getElementById('fieldoptions' + currentrow.toString());
            fieldoptions.id = "fieldoptions" + newrow.toString();
            fieldoptions.name = "fieldoptions" + newrow.toString();

            if (chosentype == "number") {
            //Field Options - Number
                const tinyintfield = document.getElementById('tinyint' + currentrow.toString());
                tinyintfield.id = "tinyint" + newrow.toString();
                tinyintfield.name = "numbertype" + newrow.toString();
                tinyintfield.setAttribute("onClick", "updateFieldOptions('tinyint', '" + newrow.toString() + "')");
                const tinyintlabel = document.getElementById('tinyintlabel' + currentrow.toString());
                tinyintlabel.id = "tinyintlabel" + newrow.toString();
                tinyintlabel.setAttribute("for", "tinyint" + newrow.toString());
                const smallintfield = document.getElementById('smallint' + currentrow.toString());
                smallintfield.id = "smallint" + newrow.toString();
                smallintfield.name = "numbertype" + newrow.toString();
                smallintfield.setAttribute("onClick", "updateFieldOptions('smallint', '" + newrow.toString() + "')");
                const smallintlabel = document.getElementById('smallintlabel' + currentrow.toString());
                smallintlabel.id = "smallintlabel" + newrow.toString();
                smallintlabel.setAttribute("for", "smallint" + newrow.toString());
                const intfield = document.getElementById('int' + currentrow.toString());
                intfield.id = "int" + newrow.toString();
                intfield.name = "numbertype" + newrow.toString();
                intfield.setAttribute("onClick", "updateFieldOptions('int', '" + newrow.toString() + "')");
                const intlabel = document.getElementById('intlabel' + currentrow.toString());
                intlabel.id = "intlabel" + newrow.toString();
                intlabel.setAttribute("for", "int" + newrow.toString());
                const bigintfield = document.getElementById('bigint' + currentrow.toString());
                bigintfield.id = "bigint" + newrow.toString();
                bigintfield.name = "numbertype" + newrow.toString();
                bigintfield.setAttribute("onClick", "updateFieldOptions('bigint', '" + newrow.toString() + "')");
                const bigintlabel = document.getElementById('bigintlabel' + currentrow.toString());
                bigintlabel.id = "bigintlabel" + newrow.toString();
                bigintlabel.setAttribute("for", "bigint" + newrow.toString());
            } else if (chosentype == "currency") {
            //Field Options - Currency
                const lowvaluefield = document.getElementById('lowvalue' + currentrow.toString());
                lowvaluefield.id = "lowvalue" + newrow.toString();
                lowvaluefield.name = "numbertype" + newrow.toString();
                lowvaluefield.setAttribute("onClick", "updateFieldOptions('5', '" + newrow.toString() + "')");
                const lowvaluelabel = document.getElementById('lowvaluelabel' + currentrow.toString());
                lowvaluelabel.id = "lowvaluelabel" + newrow.toString();
                lowvaluelabel.setAttribute("for", "lowvalue" + newrow.toString());
                const middlevaluefield = document.getElementById('middlevalue' + currentrow.toString());
                middlevaluefield.id = "middlevalue" + newrow.toString();
                middlevaluefield.name = "numbertype" + newrow.toString();
                middlevaluefield.setAttribute("onClick", "updateFieldOptions('8', '" + newrow.toString() + "')");
                const middlevaluelabel = document.getElementById('middlevaluelabel' + currentrow.toString());
                middlevaluelabel.id = "middlevaluelabel" + newrow.toString();
                middlevaluelabel.setAttribute("for", "middlevalue" + newrow.toString());
                const highvaluefield = document.getElementById('highvalue' + currentrow.toString());
                highvaluefield.id = "highvalue" + newrow.toString();
                highvaluefield.name = "numbertype" + newrow.toString();
                highvaluefield.setAttribute("onClick", "updateFieldOptions('11', '" + newrow.toString() + "')");
                const highvaluelabel = document.getElementById('highvaluelabel' + currentrow.toString());
                highvaluelabel.id = "highvaluelabel" + newrow.toString();
                highvaluelabel.setAttribute("for", "highvalue" + newrow.toString());
            }

            //Delete Button
            const deletebutton = document.getElementById('delete' + currentrow.toString());
            deletebutton.id = "delete" + newrow.toString();
            deletebutton.setAttribute("onClick", "deleteRow(" + newrow.toString() + ")");

        }
    }
}

function validateForm() {
    let fieldcount = document.getElementById('fieldcount').value;
    const namepattern = /^[a-z0-9 ]+$/i;
    const listpattern = /^[a-z0-9 ,]+$/i;
    for (x = 0; x < fieldcount; x++) {
        // Check field name
        let name = document.getElementById('fieldname' + x).value;
        let result = name.match(namepattern);
        if (result.length < 1) {
            alert("Please limit field names to alpha-numeric characters and spaces");
            return false;
        }
        let type = document.getElementById('fieldtype' + x).value;
        if ((type == "fixedlist") || (type == "adjustlist")) {
            let listvalues = document.getElementById('fieldoptions' + x).value;
            let result = listvalues.match(listpattern);
            if (result.length < 1) {
                alert("Please limit list names to alpha-numeric characters and spaces separated by commas");
                return false;
            }
        }
    }

}