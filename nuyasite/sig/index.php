<?php require_once __DIR__ . '/include/app.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="<?= $config['APP_NAME']; ?>">
		<meta name="author" content="Owsap">

		<title><?= $config['APP_NAME']; ?></title>

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

		<!-- Icons -->
		<link rel="stylesheet" href="https://unpkg.com/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" integrity="sha512-Lb/TG0BknyoO9tScqP5a5VCTMdx1z12u8Jt7w+IzGLOfj3d/eIdJ+/YI+ypgZn/s9JXTbKql63WVm03ySct+Ig==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
		<link rel="stylesheet" href="<?= $config['APP_URL']; ?>/assets/css/style.css">

		<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.5.0/css/rowReorder.dataTables.css">

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

		<!-- Prism for Code <Pre> -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs/themes/prism.css" />
		<script src="https://cdn.jsdelivr.net/npm/prismjs/prism.js"></script>
		<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
	</head>

	<body class="d-flex flex-column min-vh-100">
		<header class="p-3 bg-dark text-white text-center">
			<div class="container">
				<h1 class="display-4"><?= $config["APP_NAME"]; ?></h1>
				<p><span class="fs-4"><?= $config["APP_DESCRIPTION"]; ?></p>
				<p class="lead mb-0"><i><?= __("SUB_TITLE_PARAGRAPH"); ?></i></p>
			</div>
			<figure class="figure mt-3">
			<img src="assets/img/m2dev.png" width="100" height="100" class="figure-img img-fluid rounded" alt="...">
				<p><?= __("M2DEV_FIGURE_TEXT"); ?></p>
				<span><?= __("APP_VERSION", $config["APP_VERSION"]); ?></span><br>
				<span><?= __("PROTO_VERSION", $config["PROTO_VERSION"]); ?></span><br>
			</figure>
		</header>

		<div class="container mt-5">
			<select id="select-language" class="mb-4 mt-4" placeholder="<?= __("LANGUAGE_SELECT"); ?>" value="<?= $config['APP_LANGUAGE']; ?>" aria-label="<?= __("LANGUAGE_SELECT"); ?>"></select>
			<?php if ($config['APP_BETA']): ?>
			<div class="alert alert-primary " role="alert">
				<span><?= __("INFO_WARNING"); ?></span>
			</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title"><?= __("GROUP_TITLE"); ?></h5>
							<p><?= __("GROUP_DESCRIPTION"); ?></p>
							<select id="item-select-group" placeholder="<?= __("GROUP_ITEM_SEARCH_PLACEHOLDER"); ?>" aria-label="<?= __("GROUP_ITEM_SEARCH_PLACEHOLDER"); ?>"></select>

							<div class="input-group mb-1 mt-1">
								<span class="input-group-text"><?= __("GROUP_NAME"); ?></span>
								<input type="text" id="group-name" class="form-control" data-value="0" placeholder="<?= __("GROUP_NAME_PLACEHOLDER"); ?>" aria-label="<?= __("GROUP_NAME_PLACEHOLDER"); ?>">
							</div>

							<div class="input-group mb-1 mt-1">
								<div class="input-group-prepend">
									<button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFAQ"><?= __("GROUP_TYPE"); ?></button>
								</div>
								<select class="form-select" id="group-type">
									<option disabled><?= __("GROUP_TYPE_NONE"); ?></option>
									<option value="" selected><?= __("GROUP_TYPE_NORMAL"); ?></option>
									<option value="pct"><?= __("GROUP_TYPE_PERCENTAGE"); ?></option>
									<option value="quest"><?= __("GROUP_TYPE_QUEST"); ?></option>
									<option value="special"><?= __("GROUP_TYPE_SPECIAL"); ?></option>
								</select>
							</div>

							<div class="form-check form-switch mt-3">
								<input class="form-check-input" type="checkbox" id="showItemNameCommentCheck">
								<label class="form-check-label" for="showItemNameCommentCheck"><?= __("CHECKBOX_SHOW_ITEM_NAME_ON_GROUP"); ?></label>
							</div>

							<div class="form-check form-switch mt-3">
								<input class="form-check-input" type="checkbox" id="showOriYangExpName">
								<label class="form-check-label" for="showOriYangExpName"><?= __("CHECKBOX_SHOW_ORIGINAL_GOLD_EXP_NAME"); ?></label>
							</div>

							<div class="form-check form-switch mt-3">
								<input class="form-check-input" type="checkbox" id="ansiCheck">
								<label class="form-check-label" for="ansiCheck" checked><?= __("CHECKBOX_ANSI"); ?></label>
							</div>

							<div id="group-item_desc">
							</div>
						</div>
					</div>

					<div class="card mb-1 mt-1">
						<div class="card-body">
							<h5 class="card-title"><?= __("ITEM_TITLE"); ?></h5>
							<p><?= __("ITEM_PARAGRAPH"); ?></p>
							<select id="item-select" placeholder="<?= __("ITEM_SEARCH_PLACEHOLDER"); ?>" aria-label="<?= __("ITEM_SEARCH_PLACEHOLDER"); ?>"></select>

							<div class="input-group mb-1 mt-1">
								<span class="input-group-text"><?= __("ITEM_AMOUNT"); ?></span>
								<input type="text" id="count-input" class="form-control" placeholder="<?= __("ITEM_AMOUNT_PLACEHOLDER"); ?>" aria-label="<?= __("ITEM_AMOUNT_PLACEHOLDER2"); ?>" pattern="[0-9]*">

								<span class="input-group-text"><?= __("ITEM_PART_PCT"); ?></span>
								<input type="text" id="part-pct-input" class="form-control" placeholder="<?= __("ITEM_PART_PCT_PLACEHOLDER1"); ?>" aria-label="<?= __("ITEM_PART_PCT_PLACEHOLDER2"); ?>" pattern="[0-9]*">

								<span class="input-group-text"><?= __("ITEM_RARE_PCT"); ?></span>
								<input type="text" id="rare-pct-input" class="form-control" placeholder="<?= __("ITEM_RARE_PCT_PLACEHOLDER1"); ?>" aria-label="<?= __("ITEM_RARE_PCT_PLACEHOLDER2"); ?>" pattern="[0-9]*">
							</div>

							<button class="btn btn-success btn-block w-100" type="button" id="addItem"><?= __("ITEM_ADD_BUTTON"); ?></button>
							<div class="form-check form-switch mt-3">
								<input class="form-check-input" type="checkbox" id="resetItemSearchCheck">
								<label class="form-check-label" for="resetItemSearchCheck"><?= __("RESET_SEARCH_AFTER_ADDING_ITEM"); ?></label>
							</div>
						</div>
					</div>

					<div class="mt-2">
						<table class="table" id="group">
							<thead>
								<tr>
									<th scope="col"><?= __("TABLE_ITEM_POS"); ?></th>
									<th id="table-w-capture" scope="col"><?= __("TABLE_ITEM_VNUM"); ?></th>
									<th id="table-w-capture" scope="col"><?= __("TABLE_ITEM_AMOUNT"); ?></th>
									<th id="table-w-capture" scope="col"><?= __("TABLE_ITEM_PART_PCT"); ?></th>
									<th id="table-w-capture" scope="col"><?= __("TABLE_ITEM_RARE_PCT"); ?></th>
									<th scope="col"><i class="bi bi-image-fill"></i><?php /*echo LC_TEXT("TABLE_ITEM_ICON");*/ ?></th>
									<th scope="col"><i class="bi bi-gear"></i></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<button class="btn btn-success btn-block w-100 mb-3 mt-3" type="button" id="export-btn"><?= __("EXPORT_BUTTON"); ?></button>
				</div>

				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title"><?= __("GROUP_RESULT_TITLE"); ?></h5>

							<div id="group-block" class="group-block">
								<i class="bi bi-clipboard copy-icon copy-to-clipboard" data-clipboard-target="#group-block"></i>
								<pre><code class="language-none" id="group-output"><?= __("GROUP_BLOCK_OUTPUT_PLACEHOLDER"); ?></code></pre>
							</div>
						</div>
					</div>

					<div class="card mb-1 mt-1">
						<div class="card-body">
							<h5 class="card-title"><?= __("IMPORT_TITLE"); ?></h5>
							<div class="form-check form-switch mt-3">
								<input class="form-check-input" type="checkbox" id="save-group-name">
								<label class="form-check-label" for="save-group-name"><?= __("SAVE_GROUP_NAME_LABEL"); ?></label>
							</div>
							<p><?= __("IMPORT_PARAGRAPH"); ?></p>
							<div class="alert alert-warning alert-dismissible fade show" role="alert">
								<span><?= __("IMPORT_WARNING_ALERT"); ?></span>
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= __("CLOSE_ALERT"); ?>"></button>
							</div>
							<div class="form-floating float-right">
								<textarea class="form-control" placeholder="<?= __("IMPORT_TEXTAREA_PLACEHOLDER"); ?>" id="group-import" style="height: 500px"></textarea>
								<label for="floatingTextarea2"><?= __("IMPORT_TEXTAREA_PLACEHOLDER"); ?></label>
							</div>
							<button class="btn btn-primary mt-2 btn-block w-100" type="button" id="import-btn"><?= __("IMPORT_BUTTON"); ?></button>
						</div>
					</div>

				</div>
			</div>
		</div>

		<!-- FAQ Modal -->
		<div class="modal fade" id="modalFAQ" tabindex="-1" role="dialog" aria-labelledby="modalFAQ" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="col-12 modal-title text-center"><?= __("MODAL_FAQ_TITLE"); ?></h3>
					</div>
					<div class="accordion" id="accordionFAQ">
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingOne">
								<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"><?= __('MODAL_FAQ_GROUP_TYPE_NORMAL_TITLE'); ?></button>
							</h2>
							<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
								<div class="accordion-body"><?= __('MODAL_FAQ_GROUP_TYPE_NORMAL_BODY'); ?></div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingTwo">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"><?= __('MODAL_FAQ_GROUP_TYPE_PCT_TITLE'); ?></button>
							</h2>
							<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
								<div class="accordion-body"><?= __('MODAL_FAQ_GROUP_TYPE_PCT_BODY'); ?></div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingThree">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?= __('MODAL_FAQ_GROUP_TYPE_QUEST_TITLE'); ?></button>
							</h2>
							<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
								<div class="accordion-body"><?= __('MODAL_FAQ_GROUP_TYPE_QUEST_BODY'); ?></div>
							</div>
						</div>
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingFour">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour"><?= __('MODAL_FAQ_GROUP_TYPE_SPECIAL_TITLE'); ?></button>
							</h2>
							<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFAQ">
								<div class="accordion-body"><?= __('MODAL_FAQ_GROUP_TYPE_SPECIAL_BODY'); ?></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __("CLOSE"); ?></button>
					</div>
				</div>
			</div>
		</div>

		<footer class="bg-dark text-white text-center py-3 mt-auto">
			<div class="container">
				<p>&copy; <?= date("Y"); ?> <a href="https://owsap.dev/">Owsap Development</a>. <?= __("COPYRIGHT"); ?></p>
				<span><?= __("COPYRIGHT_PARAGRAPH"); ?></span>
			</div>
		</footer>

		<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

		<script type="text/javascript" charset="utf8" src="<?= $config['APP_URL']; ?>/assets/js/item-group-editor.js"></script>

		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/rowreorder/1.5.0/js/dataTables.rowReorder.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/rowreorder/1.5.0/js/rowReorder.dataTables.js"></script>

		<script>
			var languages = [
				<?php foreach ($config['APP_LOCALES'] as $i => $locale): ?>
				{
					value: "<?= $locale ?>",
					text: "<?= __("LANGUAGE_" . strtoupper($locale)) ?>",
					image: "assets/img/flag/<?= $locale ?>.png"
				}<?= $i < count($config['APP_LOCALES']) - 1 ? ',' : '' ?>
				<?php endforeach; ?>
			];

			//
			// Language Select
			//
			var languageSelect = document.querySelector("#select-language");
			var tomLanguageSelect = new TomSelect(languageSelect, {
				valueField: 'value',
				labelField: 'text',
				value: "<?= $_SESSION['language'] ?? $config['APP_LANGUAGE']; ?>",
				searchField: ['value', 'text'],
				options: languages,
				create: false,
				render: {
					option: function(item, escape) {
						return '<div>' +
								'<img src="' + item.image + '" class="p-2">' +
								'<span class="title">' + escape(item.text) + '</span>' +
								'</div>';
					},
					item: function(item, escape) {
						return '<div>' +
								'<img src="' + item.image + '" class="p-2">' +
								'<span class="title">' + escape(item.text) + '</span>' +
								'</div>';
					}
				},
				load: function(query, callback) {
					if (!query.length) return callback();
					var results = languages.filter(function(option) {
						return option.text.toLowerCase().includes(query.toLowerCase());
					});
					callback(results);
				},
				onChange: function(value) {
					var option = languages.find(function(item) {
						return item.value === value;
					});
					if (option) {
						ChangeLanguage(option.value)
					}
				}
			});
			tomLanguageSelect.setValue("<?= $_SESSION['language'] ?? $config['APP_LANGUAGE']; ?>", true);

			var items = [ <?= GetItemNamesJS(); ?> ];

			//
			// Item Group Select
			//
			var groupName = document.querySelector("#group-name");
			var itemSelectGroup = document.querySelector("#item-select-group");
			var tomItemSelectGroup = new TomSelect(itemSelectGroup, {
				valueField: 'value',
				labelField: 'text',
				searchField: ['value', 'text'],
				options: items,
				create: false,
				render: {
					option: function(item, escape) {
						return '<div>' +
								'<img src="' + item.image + '" />' +
								'<span class="title d-block">' + escape(item.text) + '<br>' +
								'<small>VNUM: ' + escape(item.value) + '</small></span>' +
								'</div>';
					},
					item: function(item, escape) {
						return '<div>' +
								'<img src="' + item.image + '" />' +
								'<input type="hidden" id="option-item-name" value="' + escape(item.text) + '"/>' +
								'<span class="title d-block">' + escape(item.text) + '<br>' +
								'<small>VNUM: ' + escape(item.value) + '</small></span>' +
								'</div>';
					}
				},
				load: function(query, callback) {
					if (!query.length) return callback();
					var results = items.filter(function(option) {
						return option.text.toLowerCase().includes(query.toLowerCase());
					});
					callback(results);
				},
				onChange: function(value) {
					var option = items.find(function(item) {
						return item.value === value;
					});
					if (option) {
						groupName.value = option.text.replace(/\s/g, '');
						groupName.setAttribute("data-value", option.value);
						GetItemDescription(option.value);
						ExportTableToCode();
					} else {
						groupName.value = '';
					}
				}
			});

			//
			// Item Select
			//
			var itemSelect = document.querySelector("#item-select");
			var tomItemSelect = new TomSelect(itemSelect, {
				valueField: 'value',
				labelField: 'text',
				searchField: ['value', 'text'],
				options: items,
				create: false,
				render: {
					option: function(item, escape) {
						return '<div>' +
								'<img src="' + item.image + '" />' +
								'<span class="title d-block">' + escape(item.text) + '<br>' +
								'<small>VNUM: ' + escape(item.value) + '</small></span>' +
								'</div>';
					},
					item: function(item, escape) {
						return '<div>' +
								'<img src="' + item.image + '" />' +
								'<span class="title d-block">' + escape(item.text) + '<br>' +
								'<small>VNUM: ' + escape(item.value) + '</small></span>' +
								'</div>';
					}
				},
				load: function(query, callback) {
					if (!query.length) return callback();
					var results = items.filter(function(option) {
						return option.text.toLowerCase().includes(query.toLowerCase());
					});
					callback(results);
				}
			});

			$(document).ready(function () {
				$('#group').DataTable({
					"language": {
						"url": "<?php echo GetDataTableLanguage(); ?>"
					},
					rowReorder: true
				});

				$('#count-input, #part-pct-input, #rare-pct-input').on('input', function() {
					var val = this.value;
					this.value = val.replace(/[^0-9]/g, '');
				});

				$("#modalFAQ").modal();

				const showItemNameCheck = document.getElementById('showItemNameCommentCheck');
				showItemNameCheck.addEventListener('click', ExportTableToCode);

				const showOriYangExpName = document.getElementById('showOriYangExpName');
				showOriYangExpName.addEventListener('click', ExportTableToCode);

				const ansiCheck = document.getElementById('ansiCheck');
				ansiCheck.addEventListener('click', ExportTableToCode);

				new Clipboard('.copy-to-clipboard');
			});
	</script>
		</script>
	</body>
</html>
