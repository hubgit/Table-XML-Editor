<?

class Table {
  private $ns = 'org.hubmed.headings';

  function __construct($file, $data = NULL){
    $this->file = $file;

    if ($data) { // save POSTed HTML table as XML
      $this->html_to_xml($data);
      $this->save();
    }
    else { // load an existing HTML file
      if (file_exists($this->file))
        $this->load();
    }

    $this->xml_to_html();
  }

  function load(){
    $this->xml = new DOMDocument;
    $this->xml->preserveWhiteSpace = FALSE;
    $this->xml->load($this->file);
    $this->xml->formatOutput = TRUE;
    $this->xml->encoding = 'UTF-8';
  }

  function save(){
    $this->xml->save($this->file);
  }

  function html_to_xml($data){
    $this->xml = new DOMDocument;
    $this->xml->preserveWhiteSpace = FALSE;
    $this->xml->formatOutput = TRUE;
    $this->xml->encoding = 'UTF-8';
    
    $html = new DOMDocument;
    $html->preserveWhiteSpace = FALSE;
    $html->loadHTML('<html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>' . $data . '</body></html>');

    $xpath = new DOMXPath($html);

    $table = $xpath->query('//table')->item(0);
    $root = $this->xml->appendChild($this->xml->createElement($table->getAttribute('data-element')));
    
    // create the headings block from thead
    $headings = $root->appendChild($this->xml->createElementNS($this->ns, 'headings'));
    foreach ($xpath->query('thead/tr/th', $table) as $th){
      if (!$th->hasAttribute('data-field'))
        continue;

      $h = $headings->appendChild($this->xml->createElementNS($this->ns, 'h'));
      $h->setAttribute('element', $th->getAttribute('data-field'));
      $h->setAttribute('title', $th->textContent);
    }

    // read each row as an element
    foreach ($xpath->query('tbody/tr', $table) as $row){
      $item = $root->appendChild($this->xml->createElement($row->getAttribute('data-element')));

      // read each cell as a property
      foreach ($xpath->query('td/div', $row) as $cell){
        // clean up trailing "br" elements
        if ($cell->lastChild->nodeName == 'br')
          $cell->removeChild($cell->lastChild);

        // remove carriage returns added to empty divs
        if (preg_match('/^\s*$/', $cell->textContent))
          continue;

        // create XML element
        $property = $item->appendChild($this->xml->createElement($cell->getAttribute('data-property')));

        // move contents of the cell into the new XML element
        if ($cell->hasChildNodes())
          foreach ($cell->childNodes as $childNode)
            $property->appendChild($this->xml->importNode($childNode, TRUE));
      }
    }
  }

  function xml_to_html(){
    $xpath = new DOMXPath($this->xml);
    $xpath->registerNamespace('data', $this->ns);

    // read the headings block
    $headings = $xpath->query('data:headings')->item(0);

    $fields = array();
    foreach ($xpath->query('data:h', $headings) as $node)
      $fields[$node->getAttribute('element')] = $node->getAttribute('title');

    $headings->parentNode->removeChild($headings);

    // create the HTML table
    $this->html = new DOMDocument;
    $this->html->encoding = 'UTF-8';
    $this->html->formatOutput = TRUE;

    $table = $this->html->appendChild($this->html->createElement('table'));
    $table->setAttribute('data-element', $this->xml->documentElement->nodeName);

    // create thead
    $thead = $table->appendChild($this->html->createElement('thead'));
    $row = $thead->appendChild($this->html->createElement('tr'));

    // handle
    $th = $this->html->createElement('th', ' ');
    $row->appendChild($th);

    // add a th for each field
    foreach ($fields as $field => $title){
      $th = $this->html->createElement('th', htmlspecialchars($title));
      $th->setAttribute('data-field', $field);
      $row->appendChild($th);
    }
    
    // tbody
    $tbody = $table->appendChild($this->html->createElement('tbody'));
    
    // create a tr for each element
    foreach ($xpath->query('node()') as $item){
      $row = $tbody->appendChild($this->html->createElement('tr'));
      $row->setAttribute('data-element', $item->nodeName);

      // handle
      $td = $row->appendChild($this->html->createElement('td', ' '));
      $td->setAttribute('data-handle', TRUE);     
      
      // create a td + contenteditable div for each property
      foreach (array_keys($fields) as $field){
        $td = $row->appendChild($this->html->createElement('td'));
        $cell = $td->appendChild($this->html->createElement('div'));
        $cell->setAttribute('data-property', $field);
        $cell->setAttribute('contenteditable', 'true');

        $nodes = $xpath->query($field, $item);
        if ($nodes->length && ($property = $nodes->item(0)) && $property->hasChildNodes())
          foreach ($property->childNodes as $childNode)
            $cell->appendChild($this->html->importNode($childNode, TRUE));
      }
    }
  }
}
