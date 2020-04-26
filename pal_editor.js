const add_entry = () => {
	let last_entry = document.getElementById("last_entry");
	let num = last_entry.value;
	last_entry.setAttribute("value", parseInt(num) + 1);
	
	let row = document.createElement("tr");
	row.id = "row_" + num;
	
	let key_cell = document.createElement("td");
	let key_field = document.createElement("input");
	key_field.setAttribute("type", "text");
	key_field.setAttribute("size", "20");
	key_field.setAttribute("id", "colors_" + num + "_name");
	key_field.setAttribute("name", "colors[" + num + "][name]");
	key_cell.appendChild(key_field);
	row.appendChild(key_cell);
	
	let value_cell = document.createElement("td");
	let text = document.createTextNode("#");
	value_cell.appendChild(text);
	let value_field = document.createElement("input");
	value_field.setAttribute("type", "text");
	value_field.setAttribute("size", "10");
	value_field.setAttribute("id", "colors_" + num + "_color");
	value_field.setAttribute("name", "colors[" + num + "][color]");
	value_field.onchange = () => {
		update_preview(num)
	};
	value_cell.appendChild(value_field);
	row.appendChild(value_cell);
	
	let preview_cell = document.createElement("td");
	preview_cell.style.border = "1px solid black";
	preview_cell.style.paddingLeft = "10px";
	preview_cell.setAttribute("id", "preview_" + num);
	row.appendChild(preview_cell);

	let delete_cell = document.createElement("td");
	let delete_button = document.createElement("input");
	delete_button.setAttribute("type", "button");
	delete_button.onclick = () => {
		delete_row(num);
	};
	delete_button.value = "Delete";
	delete_cell.appendChild(delete_button);
	row.appendChild(delete_cell);

	let last_row = document.getElementById("last_row");
	let table = last_row.parentNode;
	table.insertBefore(row, last_row);
}

const delete_row = (row_id) => {
	let row = document.getElementById("row_" + row_id);
	let table = row.parentNode;
	table.focus();
	table.removeChild(row);
}

const update_preview = (row_id) => {
	let preview = document.getElementById("preview_" + row_id);
	preview.style.backgroundColor = "#" + document.getElementById("colors_" + row_id + "_color").value;
}