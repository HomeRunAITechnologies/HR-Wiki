<?php
/** @var string $title */
/** @var string $pageTitle */
/** @var string $slug */
/** @var string $content */
/** @var string $format (optional, defaults to 'html') */
/** @var bool $isNew */
/** @var array $errors (optional) */
use WikiApp\Lib\Utils;
use WikiApp\Lib\Csrf;

$actionUrl = $isNew ? Utils::url('/store') : Utils::url('/update/' . htmlspecialchars($slug));
$submitButtonText = $isNew ? 'Create Page' : 'Update Page';
$pageFormat = $format ?? 'html';
?>

<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
/* Editor Container */
.editor-container {
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.editor-container .ql-container {
    min-height: 400px;
    font-size: 16px;
}
.editor-container .ql-editor {
    min-height: 380px;
}
/* Custom font support */
.ql-font-sans-serif { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }
.ql-font-serif { font-family: Georgia, "Times New Roman", serif; }
.ql-font-monospace { font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; background-color: #f8f9fa; padding: 0.1em 0.3em; border-radius: 3px; }
/* Font picker labels */
.ql-snow .ql-picker.ql-font .ql-picker-label::before, .ql-snow .ql-picker.ql-font .ql-picker-item::before { content: 'Sans Serif'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="serif"]::before, .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="serif"]::before { content: 'Serif'; font-family: Georgia, serif; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="monospace"]::before, .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="monospace"]::before { content: 'Monospace'; font-family: monospace; }
/* Emoji button */
.ql-emoji::before { content: "😀"; }
/* Emoji Picker */
.emoji-picker-container { position: absolute; z-index: 1000; background: #fff; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; width: 320px; max-height: 350px; }
.emoji-picker-container.show { display: block; }
.emoji-picker-header { padding: 8px 12px; border-bottom: 1px solid #eee; }
.emoji-picker-search { width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; }
.emoji-picker-categories { display: flex; padding: 6px; border-bottom: 1px solid #eee; overflow-x: auto; gap: 2px; }
.emoji-category-btn { padding: 4px 8px; border: none; background: none; cursor: pointer; font-size: 16px; border-radius: 4px; opacity: 0.6; }
.emoji-category-btn:hover, .emoji-category-btn.active { background: #f0f0f0; opacity: 1; }
.emoji-picker-emojis { padding: 8px; max-height: 220px; overflow-y: auto; display: grid; grid-template-columns: repeat(8, 1fr); gap: 2px; }
.emoji-item { padding: 4px; font-size: 20px; cursor: pointer; border-radius: 4px; text-align: center; line-height: 1.2; }
.emoji-item:hover { background: #f0f0f0; }
</style>

<div class="card">
    <div class="card-body">
        <h1 class="card-title"><?= htmlspecialchars($title) ?></h1>

        <?php if (!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $actionUrl ?>" method="POST" id="pageForm" enctype="multipart/form-data">
            <?= Csrf::field() ?>
            <div class="mb-3">
                <label for="page_title" class="form-label">Page Title:</label>
                <input type="text" id="page_title" name="title" class="form-control" value="<?= htmlspecialchars($pageTitle) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Content:</label>
                <input type="hidden" id="content" name="content">
                <div class="editor-container">
                    <div id="editor"><?= $content ?></div>
                    <div id="emoji-picker" class="emoji-picker-container">
                        <div class="emoji-picker-header">
                            <input type="text" class="emoji-picker-search" placeholder="Search emojis...">
                        </div>
                        <div class="emoji-picker-categories">
                            <button type="button" class="emoji-category-btn active" data-category="smileys" title="Smileys">😀</button>
                            <button type="button" class="emoji-category-btn" data-category="people" title="People">👋</button>
                            <button type="button" class="emoji-category-btn" data-category="animals" title="Animals">🐱</button>
                            <button type="button" class="emoji-category-btn" data-category="food" title="Food">🍕</button>
                            <button type="button" class="emoji-category-btn" data-category="travel" title="Travel">✈️</button>
                            <button type="button" class="emoji-category-btn" data-category="activities" title="Activities">⚽</button>
                            <button type="button" class="emoji-category-btn" data-category="objects" title="Objects">💡</button>
                            <button type="button" class="emoji-category-btn" data-category="symbols" title="Symbols">❤️</button>
                        </div>
                        <div class="emoji-picker-emojis"></div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="format" value="html">

            <?php if (!empty($allCategories)): ?>
            <div class="mb-3">
                <label for="categories" class="form-label">Categories:</label>
                <select id="categories" name="categories[]" class="form-select" multiple size="5">
                    <?php foreach ($allCategories as $cat): ?>
                        <option value="<?= htmlspecialchars((string)$cat->getId()) ?>"
                            <?= in_array((int)$cat->getId(), $assignedCategories ?? []) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Hold Ctrl/Cmd to select multiple categories.</div>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="visibility" class="form-label">Visibility:</label>
                <select id="visibility" name="visibility" class="form-select">
                    <option value="public" <?= (($visibility ?? 'public') === 'public') ? 'selected' : '' ?>>Public</option>
                    <option value="private" <?= (($visibility ?? '') === 'private') ? 'selected' : '' ?>>Private (Logged-in users only)</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary"><?= $submitButtonText ?></button>
                <a href="<?= Utils::url($isNew ? '/' : '/' . htmlspecialchars($slug)) ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Quill Editor -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function() {
    // Emoji data
    const emojiData = {
        smileys: ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙','🥲','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖'],
        people: ['👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🧠','🫀','🫁','🦷','🦴','👀','👁️','👅','👄'],
        animals: ['🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐒','🐔','🐧','🐦','🐤','🐣','🐥','🦆','🦅','🦉','🦇','🐺','🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟','🦗','🕷️','🦂','🐢','🐍','🦎','🦖','🦕','🐙','🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🐋','🦈','🐊','🐅','🐆','🦓','🦍','🦧','🐘','🦛','🦏','🐪','🐫','🦒','🦘','🐃','🐂','🐄','🐎','🐖','🐏','🐑','🦙','🐐','🦌','🐕','🐩','🐈','🐓','🦃','🦚','🦜','🦢','🦩','🐇','🦝','🦨','🦡','🦫','🦦','🦥','🐁','🐀','🐿️','🦔'],
        food: ['🍏','🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🫐','🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑','🥦','🥬','🥒','🌶️','🫑','🌽','🥕','🫒','🧄','🧅','🥔','🍠','🥐','🥯','🍞','🥖','🥨','🧀','🥚','🍳','🧈','🥞','🧇','🥓','🥩','🍗','🍖','🌭','🍔','🍟','🍕','🥪','🥙','🧆','🌮','🌯','🥗','🥘','🥫','🍝','🍜','🍲','🍛','🍣','🍱','🥟','🦪','🍤','🍙','🍚','🍘','🍥','🥠','🥮','🍢','🍡','🍧','🍨','🍦','🥧','🧁','🍰','🎂','🍮','🍭','🍬','🍫','🍿','🍩','🍪','🌰','🥜','🍯','🥛','🍼','☕','🍵','🧃','🥤','🧋','🍶','🍺','🍻','🥂','🍷','🥃','🍸','🍹','🧉','🍾'],
        travel: ['🚗','🚕','🚙','🚌','🚎','🏎️','🚓','🚑','🚒','🚐','🛻','🚚','🚛','🚜','🛴','🚲','🛵','🏍️','🚨','🚔','🚍','🚘','🚖','🚡','🚠','🚟','🚃','🚋','🚞','🚝','🚄','🚅','🚈','🚂','🚆','🚇','🚊','🚉','✈️','🛫','🛬','🛩️','💺','🛰️','🚀','🛸','🚁','🛶','⛵','🚤','🛥️','🛳️','⛴️','🚢','🏠','🏡','🏢','🏬','🏣','🏤','🏥','🏦','🏨','🏪','🏫','🏩','💒','🏛️','⛪','🕌','🕍','🛕','🕋','⛩️'],
        activities: ['⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🥏','🎱','🏓','🏸','🏒','🏑','🥍','🏏','🥅','⛳','🏹','🎣','🥊','🥋','🎽','🛹','🛼','🛷','⛸️','🥌','🎿','🎯','🎳','🎮','🎰','🧩','🎨','🎬','🎤','🎧','🎼','🎹','🥁','🎷','🎺','🎸','🪕','🎻','🎲','♟️'],
        objects: ['💡','🔦','🏮','📔','📕','📖','📗','📘','📙','📚','📓','📒','📃','📜','📄','📰','📑','🔖','💰','💴','💵','💶','💷','💸','💳','✉️','📧','📨','📩','📤','📥','📦','📫','📪','📬','📭','📮','✏️','✒️','🖋️','🖊️','🖌️','🖍️','📝','💼','📁','📂','📅','📆','📇','📈','📉','📊','📋','📌','📍','📎','🖇️','📏','📐','✂️','🔒','🔓','🔑','🗝️','🔨','🪓','⛏️','🔧','🔩','⚙️','🔗','💉','💊','🚪','🛏️','🛋️','🚽','🚿','🛁','🧹','🧺','🧻','🧼','🧽','🧯','🛒'],
        symbols: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','☸️','✡️','🔯','🕎','☯️','☦️','🛐','⛎','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','🆔','⚛️','✴️','🆚','💮','🉐','㊙️','㊗️','🈴','🈵','🈹','🈲','🅰️','🅱️','🆎','🆑','🅾️','🆘','❌','⭕','🛑','⛔','📛','🚫','💯','💢','♨️','🚷','🚯','🚳','🚱','🔞','📵','🚭','❗','❕','❓','❔','‼️','⁉️','🔅','🔆','〽️','⚠️','🚸','🔱','⚜️','🔰','♻️','✅','🈯','💹','❇️','✳️','❎','🌐','💠','🔘','🔴','🟠','🟡','🟢','🔵','🟣','⚫','⚪','🟤','🔺','🔻','🔸','🔹','🔶','🔷','🔳','🔲','▪️','▫️','◾','◽','◼️','◻️','🟥','🟧','🟨','🟩','🟦','🟪','⬛','⬜','🟫']
    };

    // Register custom fonts
    var Font = Quill.import('formats/font');
    Font.whitelist = ['sans-serif', 'serif', 'monospace'];
    Quill.register(Font, true);

    // Initialize Quill
    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Start writing your page content...',
        modules: {
            toolbar: {
                container: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'font': ['sans-serif', 'serif', 'monospace'] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image', 'video', 'emoji'],
                    ['clean']
                ],
                handlers: {
                    emoji: function() { toggleEmojiPicker(); },
                    image: function() { imageHandler(); }
                }
            }
        }
    });

    // Image upload handler
    function imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = function() {
            const file = input.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('<?= Csrf::TOKEN_NAME ?>', '<?= Csrf::generateToken() ?>');

                fetch('<?= Utils::url('/api/upload') ?>', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', data.url);
                    } else {
                        alert(data.error || 'Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Upload failed');
                });
            }
        };
    }

    // Emoji picker
    const emojiPicker = document.getElementById('emoji-picker');
    const emojiContainer = emojiPicker.querySelector('.emoji-picker-emojis');
    const emojiSearch = emojiPicker.querySelector('.emoji-picker-search');
    const categoryBtns = emojiPicker.querySelectorAll('.emoji-category-btn');
    let currentCategory = 'smileys';

    function renderEmojis(emojis) {
        emojiContainer.innerHTML = emojis.map(e => `<span class="emoji-item" data-emoji="${e}">${e}</span>`).join('');
    }

    function toggleEmojiPicker() {
        const toolbar = document.querySelector('.ql-toolbar');
        const toolbarRect = toolbar.getBoundingClientRect();
        emojiPicker.style.top = (toolbarRect.bottom + window.scrollY + 5) + 'px';
        emojiPicker.style.left = (toolbarRect.right - 320 + window.scrollX) + 'px';
        emojiPicker.classList.toggle('show');
        if (emojiPicker.classList.contains('show')) {
            renderEmojis(emojiData[currentCategory]);
            emojiSearch.focus();
        }
    }

    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            categoryBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.dataset.category;
            emojiSearch.value = '';
            renderEmojis(emojiData[currentCategory]);
        });
    });

    emojiSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        if (!query) { renderEmojis(emojiData[currentCategory]); return; }
        let results = [];
        Object.values(emojiData).forEach(emojis => { results = results.concat(emojis); });
        renderEmojis(results.slice(0, 64));
    });

    emojiContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('emoji-item')) {
            const emoji = e.target.dataset.emoji;
            const range = quill.getSelection(true);
            quill.insertText(range.index, emoji);
            quill.setSelection(range.index + emoji.length);
            emojiPicker.classList.remove('show');
        }
    });

    document.addEventListener('click', function(e) {
        if (!emojiPicker.contains(e.target) && !e.target.closest('.ql-emoji')) {
            emojiPicker.classList.remove('show');
        }
    });

    renderEmojis(emojiData.smileys);

    // Update hidden input before form submit
    document.getElementById('pageForm').addEventListener('submit', function() {
        document.getElementById('content').value = quill.root.innerHTML;
    });
})();
</script>
