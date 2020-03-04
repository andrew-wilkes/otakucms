function clk(id) {
	if (document.images) { 
    (new Image()).src= rootPath + "/?class=Click&id=" + id;
  }
  return true;
}