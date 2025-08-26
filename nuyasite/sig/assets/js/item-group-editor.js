var import_group_name = "None"

const addItem = document.getElementById("addItem");
addItem.addEventListener("click", AppendDataToTable);
function AppendDataToTable() {
	var value = tomItemSelect.getValue();
	var option = tomItemSelect.options[value];

	var count = $('#count-input').val() ? $('#count-input').val() : 1;
	var partPct = $('#part-pct-input').val() ? $('#part-pct-input').val() : 100;
	var rarePct = $('#rare-pct-input').val() ? $('#rare-pct-input').val() : "";

	var table = $('#group').DataTable();
	table.row.add([
		table.data().length + 1, // row index
		option.value,
		count,
		partPct,
		rarePct,
		'<img src="' + option.image + '" alt="' + option.text + '">',
		'<button class="btn btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>'
	]).draw(false);

	// Add event listener to the newly added remove button
	$('#group tbody').on('click', '.remove-row', function() {
		var row = table.row($(this).parents('tr'));
		row.remove().draw();
		UpdateRowIndexColumn();

		ExportTableToCode();
	});

	UpdateRowIndexColumn();
	ExportTableToCode();

	const resetItemSearch = document.getElementById("resetItemSearchCheck")
	if (resetItemSearch.checked) {
		tomItemSelect.clear();
	}
}

const exportButton = document.getElementById("export-btn");
exportButton.addEventListener("click", ExportTableToCode);
function ExportTableToCode() {
	var groupName = document.querySelector("#group-name");
	var groupNameValue = $(groupName).val() || "None";

	var saveImportGroupName = document.querySelector("#save-group-name");
	if (saveImportGroupName.checked) {
		if (import_group_name !== "None") {
			groupNameValue = import_group_name;
		}
	}

	var groupType = document.querySelector("#group-type");
	var groupTypeValue = groupType.value;

	var itemName = "";
	const showItemNameCheck = document.getElementById("showItemNameCommentCheck");
	const showOriYangExpName = document.getElementById("showOriYangExpName");
	const ansiCheck = document.getElementById("ansiCheck");

	// Get all the data in the table
	var data = $('#group').DataTable().rows().data();

	// Loop through the data and generate the output string
	var output = "";
	if (showItemNameCheck.checked) {
		var optionItemName = document.querySelector("#option-item-name");
		var optionItemNameValue = optionItemName ? optionItemName.value : "";
		if (optionItemNameValue !== "") {
			itemName = " " + "-- " + optionItemNameValue;
		} else {
			itemName = "";
		}

		output = "Group" + "\t" + groupNameValue + "\n" +
			"{\n" +
			"\t" + "Vnum" + "\t" + groupName.getAttribute("data-value") + itemName + "\n";
	}
	else
	{
		output = "Group" + "\t" + groupNameValue + "\n" +
			"{\n" +
			"\t" + "Vnum" + "\t" + groupName.getAttribute("data-value") + "\n";
	}

	if (groupTypeValue != "")
		output += "\t" + "Type" + "\t" + groupTypeValue + "\n";

	var i = 1
	data.each(function(row) {
		if (showItemNameCheck.checked) {
			var option = items.find(item => item.value === row[1]);
			if (option)
				itemName = " " + "-- " + option.text;
			else
				itemName = ""
		}

		var row_item = row[1];
		if (showOriYangExpName.checked) {
			if (ansiCheck.checked)
			{
				if (row_item == "exp")
					row_item = "°æÇèÄ¡";

				if (row_item == "gold")
					row_item = "µ·²Ù·¯¹Ì";
			}
			else
			{
				if (row_item == "exp")
					row_item = "경험치";

				if (row_item == "gold")
					row_item = "돈꾸러미";
			}
		}

		if (row[4] && row[4] > 0) {
			output += "\t" + i + "\t" + row_item + "\t" + row[2] + "\t" + row[3] + "\t" + row[4] + itemName + "\n";
		} else {
			output += "\t" + i + "\t" + row_item + "\t" + row[2] + "\t" + row[3] + itemName + "\n";
		}
		++i;
	});
	output += "}";

	// Select the code block and set its text content to the output
	var codeBlock = document.querySelector("#group-output");
	codeBlock.textContent = output;
}

const importButton = document.getElementById("import-btn");
importButton.addEventListener("click", ImportGroup);
function ImportGroup() {
	var groupImportElement = document.querySelector("#group-import");
	var data = groupImportElement.value;

	var groupNameElement = document.querySelector("#group-name");
	var groupVNumRegex = /Vnum\s+(\d+)/;
	var groupVnum = '';
	data.split("\n").forEach(function(line) {
		if (groupVNumRegex.test(line)) {
			var matches = line.match(groupVNumRegex);
			groupVnum = matches[1];

			// Extract the group name from the first line
			var lengthOfGroupNamePlaceHolder = 6
			//var groupName = data.split("\n")[0].substring(lengthOfGroupNamePlaceHolder, data.split("\n")[0].length - 1);
			var groupName = data.split("\n")[0].substring(lengthOfGroupNamePlaceHolder).trim();
			import_group_name = groupName.replace(/\s+/g, '');

			groupNameElement.value = groupName.replace(/\s+/g, '');
			groupNameElement.setAttribute("data-value", groupVnum);

			var itemSelectGroup = document.querySelector("#item-select-group").tomselect;
			itemSelectGroup.setValue(groupVnum);
		}
	});

	var groupTypeSelectElement = document.querySelector("#group-type");
	groupTypeSelectElement.value = "";

	var groupTypeRegex = /Type\s+(\w+)/;
	var groupType = '';
	data.split("\n").forEach(function (line) {
		if (groupTypeRegex.test(line)) {
			var matches = line.match(groupTypeRegex);
			groupType = matches[1];
			if (groupType)
				groupTypeSelectElement.value = groupType.toLowerCase();
		}
	});

	const ansiCheck = document.getElementById("ansiCheck");

	// Use a regular expression to extract the data
	//var dataRegex = /(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s?(\d+)?/; //v1
	// var dataRegex = /(\d+)\s+([a-zA-Z0-9]+)\s+(\d+)\s+(\d+)\s?(\d+)?/; //v2 (stable but limited to [a-zA-Z0-9])
	var dataRegexNormal = /(\d+)\s+([\s\S]+)\s+(\d+)\s+(\d+)\s?(\d+)?/; // v3 (experimental, [\s\S])
	var dataRegexRarePct = /(\d+)\s+([\s\S]+)\s+(\d+)\s+(\d+)\s+(\d+)\s?(\d+)?/; // v3 (experimental, [\s\S])

	var items = [];
	data.split("\n").forEach(function(line) {
		if (dataRegexRarePct.test(line)) {
			var matches = line.match(dataRegexRarePct);
			var rarePct = "";
			if (matches[5] > 0) {
				rarePct = matches[5];
			}

			var row_item = matches[2];
			if (ansiCheck.checked)
			{
				if (row_item == "°æÇèÄ¡")
					row_item = "exp";

				if (row_item == "µ·²Ù·¯¹Ì")
					row_item = "gold";
			}
			else
			{
				if (row_item == "경험치")
					row_item = "exp";

				if (row_item == "돈꾸러미")
					row_item = "gold";
			}

			items.push({
				index: matches[1],
				vnum: row_item,
				count: matches[3],
				partPct: matches[4],
				rarePct: rarePct
			});
		}
		else if (dataRegexNormal.test(line)) {
			var matches = line.match(dataRegexNormal);
			var rarePct = "";

			var row_item = matches[2];
			if (ansiCheck.checked)
			{
				if (row_item == "°æÇèÄ¡")
					row_item = "exp";

				if (row_item == "µ·²Ù·¯¹Ì")
					row_item = "gold";
			}
			else
			{
				if (row_item == "경험치")
					row_item = "exp";

				if (row_item == "돈꾸러미")
					row_item = "gold";
			}

			items.push({
				index: matches[1],
				vnum: row_item,
				count: matches[3],
				partPct: matches[4],
				rarePct: rarePct
			});
		}
	});

	const vnums = items.map(item => item.vnum);

	$.ajax({
		url: 'api/item-icon.php?vnums=' + vnums.join(','),
		type: 'GET',
		dataType: 'json',
		success: function (iconMap) {
			const table = $('#group').DataTable();
			table.clear();

			items.forEach((item, i) => {
				const iconPath = iconMap[item.vnum] || 'share/icon/item/dummy.png';

				table.row.add([
					i + 1,
					item.vnum,
					item.count,
					item.partPct,
					item.rarePct,
					`<img src="${iconPath}" >`,
					'<button class="btn btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>'
				]);
			});

			table.draw();
			UpdateRowIndexColumn();
			ExportTableToCode();
		},
		error: function () {
			console.error('error fetching icons');
		}
	});

	// Add event listener to the newly added remove button
	$('#group tbody').on('click', '.remove-row', function () {
		const row = $('#group').DataTable().row($(this).parents('tr'));
		row.remove().draw();
		UpdateRowIndexColumn();
		ExportTableToCode();
	});
}

function UpdateRowIndexColumn() {
	$("#group tbody tr").each(function(index) {
		var row = $(this);
		var cells = row.find("td");
		cells.eq(0).text(index + 1);
	});
}

function ChangeLanguage(locale) {
	$.ajax({
		url: "change-language.php",
		type: "POST",
		data: { language: locale },
		success: function(response) {
			location.reload();
		},
		error: function(xhr, status, error) {
			console.log("Error: " + error);
		}
	});
}

function GetItemDescription(vnum) {
	var option = tomItemSelectGroup.options[vnum];

	$.ajax({
		url: "api/item-description.php",
		type: "POST",
		data: { vnum: vnum },
		success: function(desc) {
			if (desc != "")
			{
				var alertHTML = '<div class="alert alert-dark mt-2 mb-2 alert-dismissible fade show" role="alert">' +
								'<h4 class="alert-heading">' + option.text + '</h4>' +
								'<p>' + desc + '</p>' +
								'<hr>' +
								'<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
								'</div>';
				var groupItemDesc = document.querySelector("#group-item_desc");
				groupItemDesc.innerHTML = alertHTML;
			}
		},
		error: function(xhr, status, error) {
			console.log("Error: " + error);
		}
	});
}