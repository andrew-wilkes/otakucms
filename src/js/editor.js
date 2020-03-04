// On-page WSIWYG content region Editor support
// Based on the tutorials and code available from http://getcontenttools.com

(function () {
  const serverErrorMsg = 'There was a problem connecting to the server which may mean that you are currently logged out.';
  const pageId = document.getElementsByTagName("body")[0].dataset.pageId;
  
  var idata;

  ContentTools.IMAGE_UPLOADER = imageUploader;
  var editor = ContentTools.EditorApp.get();

  editor.init('*[data-editable]', 'data-name');

  editor.addEventListener('saved', function (ev) {
    // Check to see if there are any changes to save
    var regions = ev.detail().regions;
    if (Object.keys(regions).length > 0) {
      // Transform the data to the format that is compatible with OtakuCMS i.e. an array of { key, value } objects
      var regionList = [];
      Object.keys(regions).forEach(function(name) {
        regionList.push({ key: name, value: regions[name] });
      });
      // Post to the backend server
      axios.post(rootPath + '/?class=Page&method=save_regions', { pageId: pageId, regionList: regionList }).then()
      .catch( function(error) {
        alert(serverErrorMsg);
      })
    }
    // Finish editing if we are no longer auto-saving i.e. the user has pushed the save button
    if ( ! ev.detail().passive) {
      finishEditing();
    }
  });

  editor.addEventListener('cancel', function (ev) {
    new ContentTools.FlashUI('ok');
  });

  // Add support for auto-save
  editor.addEventListener('start', function (ev) {
    var _this = this;

    // Call save every 30 seconds
    function autoSave() {
      _this.save(true); // The true value keeps us in the editing state
    };
    this.autoSaveTimer = setInterval(autoSave, 30 * 1000);
  });

  editor.addEventListener('stop', function (ev) {
    // Stop the autosave
    clearInterval(this.autoSaveTimer);
    finishEditing();
  });

  function finishEditing() {
    new ContentTools.FlashUI('ok');
  }

  function imageUploader(dialog) {
    // This uses the cloudinary image Upload Widget
    var settings = {
      cloud_name: idata.cloud_name,
      upload_preset: idata.upload_preset,
      cropping: 'server',
      multiple: false
    }
    if (idata.max_image_width) {
      settings.max_image_width = idata.max_image_width
    }
    if (idata.max_image_height) {
      settings.max_image_height = idata.max_image_height
    }
    if (idata.folder) {
      settings.folder = idata.folder
    }
    cloudinary.openUploadWidget(settings, function(error, result) {
      if (error) {
        // Close the overlaid Content Tools dialog
        dialog.dispatchEvent(dialog.createEvent('cancel'));
        if (error.message !== 'User closed widget') {
          alert(error.message);
        } 
      } else {
        // console.log(result);
        if (result.length === 1) {
          dialog.save(result[0].secure_url.replace('v' + result[0].version + '/', ''),
            [result[0].width, result[0].height],
            {
              'data-ce-max-width': result[0].width
            }
          )
        } else {
          alert('Could not read the image data');
        }
      }
    });
  }

  window.addEventListener('load', function() {
    // Programatically push the edit button since we want it to be removed from the initial view rather than using the editor start method where the button remains in view
    editor._ignition.edit();
    // document.getElementsByClassName('ct-ignition__button--edit')[0].click();
    axios.get(rootPath + '/?class=Settings&method=get').then(
      function(response) {
        var settings = response.data;
        idata = settings.find(function(item) { return item.key === 'images'; });

        // Set up styles that are defined in the style sheet of the theme
        var styles = settings.find(function(item) { return item.key === 'styles'; });
        styles.value.forEach(function(style) {
          ContentTools.StylePalette.add([
            new ContentTools.Style(style.name, style.class, style.tags)
          ]);          
        });
      })
      .catch(function(error) {
        alert(error.message);
      })
  });
})();
