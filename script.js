var app = {
  editing: null,
  
  init: function(){
    app.editableNodes = $("#main table td > div[contenteditable='true']");
    app.initFocus();
    app.command("styleWithCSS", false);
            
    $("#main table tbody").sortable({ helper: app.sortHelper, handle: '[data-handle=1]' });
    
    $("#xml-toggle").click(function(e){ $("#main pre").toggle(); });
  },
  
  initFocus: function(){
    $("#buttons button").click(function(){ app.editing.focus(); });
    
    app.editableNodes.each(function(){
      $(this).focus(app.itemFocus).bind("DOMSubtreeModified", app.itemModified);
    });
    
    $("#input").focus(app.inputFocus).bind("DOMSubtreeModified", app.inputModified);
  },
  
  itemFocus: function(){
    app.editing = this;
    app.editingItem = this;
    app.itemModified();
  },

  itemModified: function(){
    $("#input").unbind("DOMSubtreeModified").html($(app.editing).html()).bind("DOMSubtreeModified", app.inputModified);
  },
  
  inputFocus: function(){
    app.editing = this;
  },
  
  inputModified: function(){
    $(app.editingItem).unbind("DOMSubtreeModified").html($(this).html()).bind("DOMSubtreeModified", app.itemModified);
  },
  
  edit: function(){
    app.editableNodes.each(function(){
      $(this).attr("contenteditable", "true");
    });
  },

  preview: function(){
    app.editableNodes.each(function(){
      $(this).attr("contenteditable", "false");
    });
  },

  command: function(command, arg){
    document.execCommand(command, false, arg);
  },

  createLink: function(href){
    if (href != "http://")
      app.command("createLink", href);
  },

  saveTable: function(){
    $("#save-table").val($("<div/>").append($("#main table").clone()).html());
    $("#save-form").submit();
  },
  
  sortHelper: function(e, row){
    var originals = row.children();
    var helper = row.clone();
    helper.children().each(function(i){
      $(this).width(originals.eq(i).width());
    });
    return helper;
  },
  
  addRow: function(){
    $("#main tbody").append(
      $("#main tr:last").clone(true).find("td > div").each(function(i){ $(this).empty(); }).end()
    );
  }
};

$().ready(app.init);
