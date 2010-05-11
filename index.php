<? header('Content-Type: text/html;charset=utf-8'); ?>
<? require dirname(__FILE__) . '/Table.php'; ?>
<? $xml_file = dirname(__FILE__) . '/data.xml'; ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>XML Table Editor</title>  
  <link rel="stylesheet" href="style.css"></script>

  <script src="/js/jquery/jquery.js"></script>
  <script src="/js/jquery/jquery-ui/jquery-ui.js"></script>
  <script src="script.js"></script>
</head>
<body>
  <div id="buttons">
    <button accesskey="b" onclick="app.command('bold')">Bold</button>
    <button accesskey="i" onclick="app.command('italic')">Italic</button>

    <button onclick="app.command('superscript')">Superscript</button>
    <button onclick="app.command('subscript')">Subscript</button>

    <button onclick="app.command('undo')">Undo</button>
    <button onclick="app.command('redo')">Redo</button>
  
    <button onclick="app.createLink(prompt('Link URL', 'http://'))">Link</button>
    <button onclick="app.command('unlink')">Unlink</button>

    <button onclick="app.addRow()">Add row</button>

    <button onclick="app.edit()">Edit</button>
    <button onclick="app.preview()">Preview</button>
    <button onclick="app.saveTable()">Save</button> 
  </div>
  
  <div id="main">    
    <? $t = new Table($xml_file, $_POST['table']); ?>    
    
    <pre><?= htmlspecialchars($t->xml->saveXML($t->xml->documentElement), ENT_QUOTES, 'UTF-8'); ?></pre>
    
    <div id="table-container">
      <?= $t->html->saveXML($t->html->documentElement); ?>
    </div>
  </div>
  
  <form id="save-form" method="POST" accept-charset="utf-8">
    <input type="hidden" name="table" id="save-table">
  </form>
</body>
</html>
