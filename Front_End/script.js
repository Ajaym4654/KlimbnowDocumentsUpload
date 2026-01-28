document.addEventListener('DOMContentLoaded', function () {

  const form = document.getElementById('uploadForm');
  const fileInput = document.getElementById('documents');
  const dropZone = document.getElementById('dropZone');
  const fileList = document.getElementById('fileList');
  const progressWrap = document.getElementById('progressWrap');
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');
  const message = document.getElementById('message');

  let files = [];

  function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function (m) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      })[m];
    });
  }

  function renderFiles() {
    fileList.innerHTML = '';
    files.forEach((file, i) => {
      const div = document.createElement('div');
      div.className = 'file-item';
      div.innerHTML = `
        <span>${escapeHtml(file.name)}</span>
        <button type="button" data-i="${i}" class="remove-btn">Remove</button>
      `;
      fileList.appendChild(div);
    });
  }

  function addFiles(newFiles) {
    for (let i = 0; i < newFiles.length; i++) {
      files.push(newFiles[i]);
    }
    renderFiles();
  }

  fileInput.addEventListener('change', e => {
    addFiles(e.target.files);
    fileInput.value = '';
  });

  dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('drag');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag');
  });

  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag');
    if (e.dataTransfer.files.length) {
      addFiles(e.dataTransfer.files);
    }
  });

  fileList.addEventListener('click', e => {
    if (e.target.classList.contains('remove-btn')) {
      const i = Number(e.target.dataset.i);
      files.splice(i, 1);
      renderFiles();
    }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    message.textContent = '';
    message.style.color = '';

    if (!files.length) {
      message.style.color = 'red';
      message.textContent = 'Please upload at least one file.';
      return;
    }

    const fd = new FormData();
    fd.append('name', document.getElementById('name').value);
    fd.append('email', document.getElementById('email').value);
    files.forEach(f => fd.append('documents[]', f));

    const xhr = new XMLHttpRequest();

    // ✅ IMPORTANT: Correct backend path
    xhr.open('POST', 'https://klimbnowdocumentsupload.onrender.com/Backend/upload.php', true);

    xhr.timeout = 120000;

    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) {
        const pct = Math.round((e.loaded / e.total) * 100);
        progressWrap.style.display = 'block';
        progressBar.style.width = pct + '%';
        progressText.textContent = 'Uploading ' + pct + '%';
      }
    };

    xhr.onerror = function () {
      progressWrap.style.display = 'none';
      message.style.color = 'red';
      message.textContent = 'Network error. Server not reachable.';
    };

    xhr.ontimeout = function () {
      progressWrap.style.display = 'none';
      message.style.color = 'red';
      message.textContent = 'Upload timeout. Try again.';
    };

    xhr.onload = function () {
      progressWrap.style.display = 'none';

      try {
        const res = JSON.parse(xhr.responseText);

        if (res.status === 'success') {
          message.style.color = 'green';
          message.textContent = '✅ Documents sent successfully!';
          form.reset();
          files = [];
          renderFiles();
          progressBar.style.width = '0%';
        } else {
          message.style.color = 'red';
          message.textContent = res.message || 'Upload failed';
        }

      } catch (e) {
        message.style.color = 'red';
        message.textContent = 'Server returned invalid response.';
      }
    };

    xhr.send(fd);
  });
});
