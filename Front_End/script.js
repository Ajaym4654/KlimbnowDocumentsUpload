document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('uploadForm');
  const fileInput = document.getElementById('documents');
  const dropZone = document.getElementById('dropZone');
  const fileList = document.getElementById('fileList');
  const progressWrap = document.getElementById('progressWrap');
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');
  const message = document.getElementById('message');

  let files = [];

  function renderFiles(){
    fileList.innerHTML = '';
    files.forEach((f, idx) => {
      const div = document.createElement('div');
      div.className = 'file-item';
      div.innerHTML = '<div class="file-name">'+escapeHtml(f.name)+'</div><button class="remove-btn" data-i="'+idx+'">Remove</button>';
      fileList.appendChild(div);
    });
  }

  function escapeHtml(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function addFiles(newFiles){
    for(let i=0;i<newFiles.length;i++){
      files.push(newFiles[i]);
    }
    renderFiles();
  }

  dropZone.addEventListener('click', ()=> fileInput.click());
  fileInput.addEventListener('change', (e)=> {
    addFiles(e.target.files);
    fileInput.value = '';
  });

  dropZone.addEventListener('dragover', (e)=> { e.preventDefault(); dropZone.classList.add('drag'); });
  dropZone.addEventListener('dragleave', (e)=> { dropZone.classList.remove('drag'); });
  dropZone.addEventListener('drop', (e)=> {
    e.preventDefault();
    dropZone.classList.remove('drag');
    if(e.dataTransfer && e.dataTransfer.files) addFiles(e.dataTransfer.files);
  });

  fileList.addEventListener('click', (e)=> {
    if(e.target.matches('.remove-btn')){
      const i = Number(e.target.getAttribute('data-i'));
      files.splice(i,1);
      renderFiles();
    }
  });

  form.addEventListener('submit', function(e){
    e.preventDefault();
    message.textContent = '';
    if(files.length === 0){ message.style.color='red'; message.textContent = 'Please add at least one file.'; return; }
    if(!navigator.onLine){ message.style.color='red'; message.textContent = 'You appear to be offline. Check your connection.'; return; }

    const fd = new FormData();
    fd.append('name', document.getElementById('name').value);
    fd.append('email', document.getElementById('email').value);
    for(let i=0;i<files.length;i++){
      fd.append('documents[]', files[i], files[i].name);
    }

    const xhr = new XMLHttpRequest();
    // dynamic URL: use form.action so it works on any domain/path
    const url = form.action;
    xhr.open('POST', url, true);
    xhr.timeout = 120000; // 2 minutes

    xhr.upload.addEventListener('progress', function(ev){
      if(ev.lengthComputable){
        const pct = Math.round((ev.loaded / ev.total) * 100);
        progressWrap.style.display = 'block';
        progressBar.style.width = pct + '%';
        progressText.textContent = 'Uploading ' + pct + '%';
      }
    });

    xhr.onerror = function(){
      progressWrap.style.display = 'none';
      message.style.color = '#b02a37';
      message.textContent = 'Network error while sending files (possible CORS, blocked request, or offline). Status: 0';
    };
    xhr.ontimeout = function(){
      progressWrap.style.display = 'none';
      message.style.color = '#b02a37';
      message.textContent = 'Upload timed out. Server may be slow or blocking uploads.';
    };

    xhr.onreadystatechange = function(){
      if(xhr.readyState === 4){
        progressWrap.style.display = 'none';
        if(xhr.status === 200){
          try{
            const res = JSON.parse(xhr.responseText);
            if(res.success){
              message.style.color = 'green';
              message.textContent = res.message || 'Uploaded successfully!';
              form.reset();
              files = [];
              renderFiles();
              progressBar.style.width = '0%';
            } else {
              message.style.color = '#b02a37';
              message.textContent = res.message || 'Upload failed';
            }
          }catch(err){
            message.style.color = '#b02a37';
            message.textContent = 'Unexpected server response: ' + xhr.responseText;
          }
        } else {
          // status 0 handled in xhr.onerror; here handle other HTTP errors
          message.style.color = '#b02a37';
          message.textContent = 'Upload failed. Server error. Status: ' + xhr.status + ' Response: ' + xhr.responseText;
        }
      }
    };

    xhr.send(fd);
  });
});
