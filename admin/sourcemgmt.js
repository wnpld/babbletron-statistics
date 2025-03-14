function startManualForm() {
    //Changes form values for annual and static entries
    //Sets section information
    //Hides options form and shows main form
    const sectionfield = document.getElementById("selectionselect");
    sectionid = sectionfield.ariaValueNow;
    sectionname = sectionfield.options[sectionfield.selectedIndex].text;
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
    //default is monthly
    document.getElementById("dsourceoptions").setAttribute("style", "display:none;");
    document.getElementById("dsourceform").setAttribute("style", "display:block;");
}

function addFormField(formtype) {
    const fieldlist = document.getElementById("dsourcelist");
    var fieldcount = document.getElementById("fieldcount").ariaValueNow;
    const newrow = document.createElement("div");
    newrow.classList.add("row");

    //Name Field
    const namecol = document.createElement("div");
    namecol.classList.add("col");
    newrow.appendChild(namecol);
    newrow.setAttribute("id", "row" + fieldcount.toString());
    const namelabel = document.createElement("label");
    namelabel.classList.add("form-label");
    namelabel.setAttribute("for", "fieldname" + fieldcount.toString());
    namelabel.setAttribute("required", "");
    namelabel.textContent = "Field Name";
    namecol.appendChild(namelabel);
    const namefield = document.createElement("input");
    namefield.setAttribute("type", "text")
    namefield.setAttribute("id", "fieldname" + fieldcount.toString());
    namefield.setAttribute("name", "fieldname" + fieldcount.toString());
    namefield.setAttribute("size", "50");
    namecol.appendChild(namefield);

    //Type Field
    const typecol = document.createElement("div");
    typecol.classList.add("col");
    newrow.appendChild(typecol);
    const typelabel = document.createElement("label");
    typelabel.classList.add("form-label");
    typelabel.setAttribute("for", "fieldtype" + fieldcount.toString());
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

    //Option Field
    //Options are highly variable and mainly setup by a different
    //script when a field type is selected.  The default setup
    //is for a number
    const optioncol = document.createElement("div");
    optionblock.setAttribute("id", "options" + fieldcount.toString());
    optioncol.classList.add("col");
    const optionblock = document.createElement("div");
    optionblock.setAttribute("id", "optionset" + fieldcount.toString());
    const fieldoptions = document.createElement("input");
    fieldoptions.setAttribute("type", "hidden");
    fieldoptions.setAttribute("id", "fieldoptions" + fieldcount.toString());
    fieldoptions.setAttribute("name", "fieldoptions" + fieldcount.toString());
    fieldoptions.value = "smallint";
    optionblock.appendChild(fieldoptions);
    const optionsselect = document.createElement("div");
    optionsselect.classList.add("form-check");
    const tinyintcheck = document.createElement("input");
    tinyintcheck.classList.add("form-check-input");
    tinyintcheck.setAttribute("type", "radio");
    tinyintcheck.setAttribute("onclick", "updateOptions('number','tinyint')");
    tinyintcheck.textContent = "Less than 200";
    optionsselect.appendChild(tinyintcheck);
    const smallintcheck = document.createElement("input");
    smallintcheck.classList.add("form-check-input");
    smallintcheck.setAttribute("type", "radio");
    smallintcheck.setAttribute("onclick", "updateOptions('number','smallint')");
    smallintcheck.textContent = "Less than 50,000";
    smallintcheck.setAttribute("checked", "");
    optionsselect.appendChild(smallintcheck);
    const intcheck = document.createElement("input");
    intcheck.classList.add("form-check-input");
    intcheck.setAttribute("type", "radio");
    intcheck.setAttribute("onclick", "updateOptions('number','smallint')");
    intcheck.textContent = "Less than 4,000,000";
    optionsselect.appendChild(intcheck);
    const bigintcheck = document.createElement("input");        
    bigintcheck.classList.add("form-check-input");
    bigintcheck.setAttribute("type", "radio");
    bigintcheck.setAttribute("onclick", "updateOptions('number','smallint')");
    bigintcheck.textContent = "More than 4,000,000";
    optionsselect.appendChild(bigintcheck);
    optionblock.appendChild(optionsselect);
    optioncol.appendChild(optionblock);
    newrow.appendChild(optioncol);

    //Delete Column
    //Has a button that can be used to delete the row
    const deletecol = document.createElement("div");
    deletecol.classList.add("col");
    newrow.appendChild(deletecol);
    const deletebutton = document.createElement("button");
    deletebutton.classList.add("btn");
    deletebutton.classList.add("btn-sm");
    deletebutton.classList.add("button-danger");
    deletebutton.setAttribute("onclick", "deleteRow(" + fieldcount.toString() + ")");
    deletebutton.textContent = "Delete";
    deletecol.appendChild(deletebutton);
    
    fieldcount++;
    document.getElementById("fieldcount").value = fieldcount;
}

function changeOptions(fieldcount) {
    const typeselect = document.getElementById("fieldtype" + fieldcount.toString());
    const datatype = typeselect.value;

    //delete the current option set and then put a new one in
    const oldblock = document.getElementById("optionset" + fieldcount.toString());
    oldblock.remove();

    const newblock = document.createElement("div");
    newblock.setAttribute("id", "optionset" + fieldcount.toString());
    document.getElementById("options").appendChild(newblock);

    if (datatype == "number") {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Choose a maximum range for this number.";
        const fieldoptions = document.createElement("input");
        fieldoptions.setAttribute("type", "hidden");
        fieldoptions.setAttribute("id", "fieldoptions" + fieldcount.toString());
        fieldoptions.setAttribute("name", "fieldoptions" + fieldcount.toString());
        fieldoptions.value = "smallint";
        newblock.appendChild(fieldoptions);
        const optionsselect = document.createElement("div");
        optionsselect.classList.add("form-check");
        const tinyintcheck = document.createElement("input");
        tinyintcheck.classList.add("form-check-input");
        tinyintcheck.setAttribute("type", "radio");
        tinyintcheck.setAttribute("onclick", "updateOptions('number','tinyint')");
        tinyintcheck.textContent = "Less than 200";
        optionsselect.appendChild(tinyintcheck);
        const smallintcheck = document.createElement("input");
        smallintcheck.classList.add("form-check-input");
        smallintcheck.setAttribute("type", "radio");
        smallintcheck.setAttribute("onclick", "updateOptions('number','smallint')");
        smallintcheck.setAttribute("checked", "");
        smallintcheck.textContent = "Less than 50,000";
        optionsselect.appendChild(smallintcheck);
        const intcheck = document.createElement("input");
        intcheck.classList.add("form-check-input");
        intcheck.setAttribute("type", "radio");
        intcheck.setAttribute("onclick", "updateOptions('number','smallint')");
        intcheck.textContent = "Less than 4,000,000";
        optionsselect.appendChild(intcheck);
        const bigintcheck = document.createElement("input");        
        bigintcheck.classList.add("form-check-input");
        bigintcheck.setAttribute("type", "radio");
        bigintcheck.setAttribute("onclick", "updateOptions('number','smallint')");
        bigintcheck.textContent = "More than 4,000,000";
        optionsselect.appendChild(bigintcheck);
        newblock.appendChild(optionsselect);
        
    } else if (datatype == "currency") {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Choose a maximum amount";
        const fieldoptions = document.createElement("input");
        fieldoptions.setAttribute("type", "hidden");
        fieldoptions.setAttribute("id", "fieldoptions" + fieldcount.toString());
        fieldoptions.setAttribute("name", "fieldoptions" + fieldcount.toString());
        fieldoptions.value = "8";
        newblock.appendChild(fieldoptions);
        const optionsselect = document.createElement("div");
        optionsselect.classList.add("form-check");
        const lowvaluecheck = document.createElement("input");
        lowvaluecheck.classList.add("form-check-input");
        lowvaluecheck.setAttribute("type", "radio");
        lowvaluecheck.setAttribute("onclick", "updateOptions('currency','5')");
        lowvaluecheck.textContent = "Up to $999";
        optionsselect.appendChild(lowvaluecheck);
        const middlevaluecheck = document.createElement("input");
        middlevaluecheck.classList.add("form-check-input");
        middlevaluecheck.setAttribute("type", "radio");
        middlevaluecheck.setAttribute("onclick", "updateOptions('currency','8')");
        middlevaluecheck.textContent = "Up to $999,999";
        middlevaluecheck.setAttribute("checked", "");
        optionsselect.appendChild(middlevaluecheck);
        const highvaluecheck = document.createElement("input");
        highvaluecheck.classList.add("form-check-input");
        highvaluecheck.setAttribute("type", "radio");
        highvaluecheck.setAttribute("onclick", "updateOptions('currency','11')");
        highvaluecheck.textContent = "up to $999,999,999";
        optionsselect.appendChild(highvaluecheck);
        newblock.appendChild(optionsselect);

    } else if (datatype == "fixedlist" ) {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Use a fixed list if you expect this list to never change.  Months of the year, days of the week, and hours are common examples of this.";
        const optionlist = document.createElement("textarea");
        optionlist.setAttribute("name", "fieldoptions" + fieldcount.toString());
        optionlist.setAttribute("aria-describedby", "optionguide" + fieldcount.toString());
        newrow.appendChild(optionlist);
        const optionguide = document.createElement("div");
        optionguide.classList("form-text");
        optionguide.setAttribute("id", "optionguide" + fieldcount.toString());
        optionguide.textContent = "Enter a list of options separated by commas. Be careful of what you put here since you can't change it later."
        newblock.appendChild(optionguide);
    } else if (datatype == "adjustlist" ) {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Use an adjustable list if you expect this list to change over time.  Material types and media formats should go here.  If you need a list and aren't sure, use this one.";
        const optionlist = document.createElement("textarea");
        optionlist.setAttribute("name", "fieldoptions" + fieldcount.toString());
        optionlist.setAttribute("aria-describedby", "optionguide" + fieldcount.toString());
        newrow.appendChild(optionlist);
        const optionguide = document.createElement("div");
        optionguide.classList("form-text");
        optionguide.setAttribute("id", "optionguide" + fieldcount.toString());
        optionguide.textContent = "Enter a list of options separated by commas."
        newblock.appendChild(optionguide);
    } else if (datatype == "date") {
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Dates are available as a type for situations where you're doing a static inventory.  Don't use this for date-based statistics.";
    } else {
        //text
        document.getElementById("typehelp" + fieldcount.toString()).textContent = "Select a maximum length for your text field.";
        const optionlabel = document.createElement("label");
        optionlabel.setAttribute("for", "fieldoptions" + fieldcount.toString());
        optionlabel.classList.add("form-label");
        optionlabel.textContent = "Maximum text length in characters";
        newblock.appendChild(optionlabel);
        const optionsize = document.createElement("input");
        optionsize.classList.new("form-control");
        optionsize.setAttribute("type", "number");
        optionsize.setAttribute("id", "fieldoptions" + fieldcount.toString());
        optionsize.setAttribute("name", "fieldoptions" + fieldcount.toString());
        optionsize.setAttribute("min", "1");
        optionsize.setAttribute("max", "200");
        optionsize.value = "50";
        newblock.appendChild("optionsize");
    }
}