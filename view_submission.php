<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Assignment | Grade Genie</title>
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@300;400;600&display=swap" rel="stylesheet">
  <?php include 'header.php'; ?>
  <?php include 'menu.php'; ?>
  <style>
    :root {
      --bg: #f9fafb;
      --surface: #ffffff;
      --primary: #28a745;
      --text-dark: #333333;
      --text-mid: #666666;
      --text-light: #888888;
      --radius: 12px;
      --spacing: 20px;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      height: 100%;
      margin: 0;
      /* overflow: hidden; */
      background: var(--bg);
      font-family: 'Albert Sans', sans-serif;
      color: var(--text-dark);
      line-height: 1.6;
    }

    /* body {
      background: var(--bg);
      font-family: 'Albert Sans', sans-serif;
      color: var(--text-dark);
      line-height: 1.6;
    } */

    #mainContent {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr;
      gap: var(--spacing);
      padding: var(--spacing);
      height: calc(100vh - 60px);
      /* <-- subtract your header height */
      /* overflow: hidden; */
    }

    .panel {
      background: var(--surface);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: var(--spacing);
      display: flex;
      flex-direction: column;
    }

    /* File Viewer */
    /* #fileViewer iframe {
      width: 100%;
      height: calc(100vh - 2 * var(--spacing) - 60px);
      border: none;
      border-radius: var(--radius);
    } */

    #fileViewer {
      background: var(--surface);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: var(--spacing);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    #pdfViewer {
      flex: 1;
      /* fill entire #fileViewer */
      overflow-y: auto;
      /* scroll only here */
      border: 1px solid #ddd;
      /* optional, to illustrate boundary */
      border-radius: var(--radius);
    }

    .pdf-page-container {
      position: relative;
      /* margin-bottom: var(--spacing); */
    }

    .pdf-page-container canvas {
      /* display: block; */
      /* ensure no inline artefacts */
      pointer-events: none;
      /* let clicks pass through to the textLayer */
    }


    /* ensure text‑layer is inside its .pdf-page-container and selectable */
    .textLayer {
      position: absolute !important;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 10;
      background: transparent;
      pointer-events: none;
      /* container ignores, so spans get the events */
      user-select: text;
      /* container itself not selectable */
    }

    .textLayer>span {
      display: inline-block;
      white-space: pre;
      /* preserve spacing/line breaks */
      user-select: text;
      pointer-events: auto;
      cursor: text;
    }


    /* Inline Comments */
    #inlineCommentsBox h3,
    #assignmentDetailsRight h3 {
      font-size: 1.25rem;
      margin-bottom: var(--spacing);
      color: var(--text-dark);
    }

    .comment-block {
      background: var(--bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: var(--spacing);
      margin-bottom: var(--spacing);
      border: 1px solid #ddd;
    }

    .comment-subject {
      font-style: italic;
      color: var(--text-light);
      margin-bottom: 8px;
    }

    .comment-body {
      margin-bottom: 12px;
    }

    .comment-footer {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
    }

    .comment-date {
      font-size: 0.85em;
      color: var(--text-mid);
    }

    #commentForm textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: var(--radius);
      resize: vertical;
    }

    .formCTA {
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: var(--radius);
      padding: 12px;
      cursor: pointer;
      font-weight: 600;
      width: 100%;
      margin-top: 12px;
    }

    /* Assignment Details */
    #assignmentDetailsRight .section {
      margin-bottom: var(--spacing);
    }

    #assignmentDetailsHeader {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--spacing);
    }

    #fileName {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text-mid);
      word-break: break-all;
    }

    h3 {
      font-weight: 600;
    }

    #assignmentTitle {
      font-size: 1.125rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .secondaryHeading {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    #scoreParent {
      margin-top: 16px;
    }

    #score {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary);
    }

    #comments {
      margin-top: 16px;
      font-size: 0.95rem;
      color: var(--text-mid);
    }

    /* Edit button adjustments */
    #editButton {
      background: var(--primary);
      color: #fff;
      border-radius: var(--radius);
      font-weight: 600;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      width: auto !important;
      /* margin: 0; */
      margin-top: -15px;
      padding: 4px 8px;
      font-size: 0.85rem;
    }

    .icon-pencil {
      margin-right: 6px;
      display: inline-block;
      vertical-align: middle;
    }

    #editFields {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: var(--spacing);
    }

    #editFields input,
    #editFields textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: var(--radius);
      font-family: inherit;
    }

    .editActions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      margin-top: 8px;
    }

    #saveButton {
      background: var(--primary);
      color: #fff;
      border-radius: var(--radius);
      padding: 8px 12px;
      font-weight: 600;
      border: none;
      cursor: pointer;
    }

    #cancelButton {
      background: #ccc;
      color: var(--text-dark);
      border-radius: var(--radius);
      padding: 8px 12px;
      font-weight: 600;
      border: none;
      cursor: pointer;
    }

    .hidden {
      display: none !important;
    }

    .comment-textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 12px;
      border: 1px solid #ddd;
      border-radius: var(--radius);
      background: var(--surface);
      font-size: 1rem;
      line-height: 1.5;
      box-shadow: var(--shadow);
      resize: vertical;
      transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .comment-textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    }

    .comment-form-box {
      border: 1px solid #ddd;
      border-radius: var(--radius);
      padding: var(--spacing);
      background: var(--surface);
      box-shadow: var(--shadow);
      margin-bottom: var(--spacing);
    }

    .comment-form-box .form-group {
      margin-bottom: var(--spacing);
    }


    .comment-form-box .form-actions {
      display: flex;
      gap: 12px;
    }


    .comment-form-box .formCTA {
      flex: 1;
    }

    .comment-form-box .formCTA.cancel {
      background: #ccc;
      color: var(--text-dark);
    }

    #commentSubtitle {
      border: none !important;
      background: transparent !important;
      box-shadow: none !important;
      resize: none;
      padding: 0;
      margin: 0;
      font-style: italic;
      text-overflow: ellipsis;
      line-height: 1.4;
      font-style: italic;
      color: var(--text-light);
      white-space: nowrap;
      /* overflow: hidden; */
      user-select: none;
      cursor: default;
    }


    .comment-form-box label[for="commentSubtitle"] {
      color: var(--text-light);
      /* same subtle mid-gray */
      font-style: italic;
      /* matches the textarea */
      margin-bottom: 4px;
      /* a little breathing room */
    }

    /* 3) Re-align the textarea so it doesn’t overflow its panel */
    .comment-form-box .form-group {
      position: relative;
    }

    @media (max-width: 1200px) {
      #mainContent {
        display: grid;
        /* ensure we’re in grid mode */
        grid-template-columns: 1fr;
        /* single column */
        grid-template-areas:
          "fileViewer"
          "assignmentDetailsRight"
          "inlineCommentsBox";
        /* move inlineCommentsBox last */
      }

      #fileViewer {
        grid-area: fileViewer;
      }

      #assignmentDetailsRight {
        grid-area: assignmentDetailsRight;
      }

      #inlineCommentsBox {
        grid-area: inlineCommentsBox;
      }

      /* #assignmentDetailsRight {
        grid-area: assignmentDetailsRight;
      } */

      /* #inlineCommentsBox {
        grid-area: inlineCommentsBox;
      } */
      @media (max-width: 768px) {
        #fileViewer {
          height: 60vh;
        }

        #pdfViewer {
          height: 100%;
        }
      }
  </style>
</head>

<body>
  <main id="mainContent">
    <!-- <section id="fileViewer" class="panel">
      <!-- <iframe id="submissionFile" src=""></iframe> -->
    <!-- <div id="pdfViewer"></div> -->
    <!-- </section> -->

    <section id="fileViewer" class="panel" style="position:relative; height:100%;">
      <!-- scroll‐frame -->
      <div id="viewerContainer" class="pdfViewerContainer" style="position:absolute;
              top:0; left:0; right:0; bottom:0;
              overflow-y:auto;
              overflow-x:auto;">
        <!-- the actual viewer now lives *inside* the scroll‐frame -->
        <div id="viewer" class="pdfViewer" style="position:relative;"></div>
      </div>
    </section>

    <section id="inlineCommentsBox" class="panel">
      <!-- Existing comments -->
      <div id="inlineCommentList" class="mt-6"></div>

      <!-- Step 1: trigger -->

      <!-- Step 2: form (hidden initially) -->

      <!-- COMMENT FORM -->
      <div id="commentFormWrapper" class="hidden">
        <div class="comment-form-box">
          <!-- Subtitle (auto-filled) -->
          <div class="form-group">
            <label for="commentSubtitle">Subject</label>
            <input type="text" id="commentSubtitle" class="comment-subtitle" readonly title="" />
          </div>
          <!-- Description (manual) -->
          <div class="form-group">
            <label for="commentDescription">Comment</label>
            <textarea id="commentDescription" class="comment-textarea" rows="3"
              placeholder="Write your comment…"></textarea>
          </div>
          <!-- Actions -->
          <div class="form-actions">
            <button id="submitCommentBtn" class="formCTA">Submit Comment</button>
            <button id="cancelCommentBtn" class="formCTA cancel">Cancel</button>
          </div>
        </div>
      </div>
      <div class="section flex justify-end mb-4">
        <button id="toggleCommentBtn" class="formCTA">Add Comment</button>
      </div>


    </section>


    <section id="assignmentDetailsRight" class="panel">
      <div id="assignmentDetailsHeader">
        <h3>Assignment Details</h3>
        <button id="editButton"><span class="icon-pencil">✏️</span>Edit</button>
      </div>

      <h4 id="assignmentTitle">Macbeth Essay Analysis</h4>
      <div id="fileName" class="section"></div>
      <div class="secondaryHeading">
        <span>Student: <strong id="studentName"></strong></span>

      </div>
      <div id="scoreParent" class="section">
        <div id="score"></div>
      </div>
      <div id="comments" class="section"></div>

      <div id="editFields" class="hidden">
        <input type="text" id="editScore" placeholder="Edit Score">
        <textarea id="editComments" rows="4" placeholder="Edit Comments"></textarea>
        <div class="editActions">
          <button id="saveButton">Save</button>
          <button id="cancelButton">Cancel</button>
        </div>
      </div>
    </section>

  </main>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf.min.js"></script>
  <!-- PDF.js viewer (TextLayerBuilder lives here) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf_viewer.min.js"></script>
  <!-- PDF.js text‑layer styles -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf_viewer.min.css" />
  <!-- Your own script that calls TextLayerBuilder -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/8.0.1/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf.worker.min.js';
    const { PDFViewer, PDFLinkService, EventBus } = window.pdfjsViewer;

    function renderPDF(url) {
      const container = document.getElementById('viewerContainer');
      const viewerElem = document.getElementById('viewer');
      const eventBus = new EventBus();
      const linkService = new PDFLinkService({ eventBus });
      const pdfViewer = new PDFViewer({
        container,
        viewer: viewerElem,
        eventBus,
        linkService,
        textLayerMode: 2
      });
      linkService.setViewer(pdfViewer);

      pdfjsLib.getDocument(url).promise.then(pdfDoc => {
        pdfViewer.setDocument(pdfDoc);
        linkService.setDocument(pdfDoc, null);
      });
    }



    $(function () {
      const submissionId = new URLSearchParams(window.location.search).get('id');
      if (!submissionId) return alert('Submission ID is missing.');

      let commentMode = false;
      let selectedText = '';

      function updateUI() {
        if (commentMode) {
          $('#toggleCommentBtn').hide();
          $('#commentFormWrapper').removeClass('hidden');
        } else {
          $('#toggleCommentBtn').show();
          $('#commentFormWrapper').addClass('hidden');
          clearForm();
        }
      }

      function clearForm() {
        selectedText = '';
        $('#commentSubtitle').val('');
        $('#commentDescription').val('');
      }



      fetchSubmissionDetails(submissionId);
      let tinymceEditor;

      $('#viewerContainer').on('mouseup', function () {
        if (!commentMode) return;
        const sel = window.getSelection().toString().trim();
        if (!sel) {
          $('#commentSubjectPreview').addClass('hidden');
          return;
        }
        // show the preview as before
        selectedText = sel;
        $('#commentSubtitle').val(sel);
        $('#commentSubjectPreview').removeClass('hidden');
      });

      // 2) Watch for mouse‑up anywhere in the right‑hand panel
      // $('#submissionFile').on('load', function () {
      // const doc = this.contentDocument || this.contentWindow.document;
      // doc.addEventListener('mouseup', () => {
      //   if (!commentMode) return;
      //   const sel = doc.getSelection().toString().trim();
      //   if (!sel) {
      //     $('#commentSubjectPreview').addClass('hidden');
      //     return;
      //   }
      //   selectedText = sel;
      //   $('#commentSubjectPreview .comment-subject')
      //     .text(`Comment on: “${sel}”`);
      //   $('#commentSubjectPreview').removeClass('hidden');
      // });
      // });

      // $('#assignmentDetailsRight').on('mouseup', function () {
      //   if (!commentMode) return;

      //   const sel = window.getSelection().toString().trim();
      //   if (!sel) {
      //     $('#commentSubjectPreview').addClass('hidden');
      //     return;
      //   }

      //   selectedText = sel;

      //   // ← this is the full line to update your comment subject:
      //   $('#commentSubjectPreview .comment-subject')
      //     .text(`Comment on: “${sel}”`);

      //   $('#commentSubjectPreview').removeClass('hidden');
      // });

      $('#submitCommentBtn').click(() => {
        const subtitle = $('#commentSubtitle').val().trim();
        const description = $('#commentDescription').val().trim();
        if (!subtitle || !description) {
          return alert('Please select text and write a comment.');
        }
        postInlineComment(subtitle, description)
          .done(res => {
            if (!res.success) {
              return alert(res.message || 'Failed to save comment.');
            }
            const c = res.comment;
            const ts = new Date(c.created_at)
              .toLocaleString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
              });
            $('#inlineCommentList').append(`
          <div class="comment-block">
            <p class="comment-subject">…${c.highlighted_text}…</p>
            <p class="comment-body">${c.comment_text}</p>
            <div class="comment-footer">
              <img src="https://i.pravatar.cc/40?u=${c.user_id || 'anon'}" class="avatar">
              <span class="comment-date">${ts}</span>
            </div>
          </div>`);
            commentMode = false;
            updateUI();
          })
          .fail(() => alert('Network error. Please try again.'));
      });

      $('#editButton').on('click', function () {
        $('#editFields').removeClass('hidden');
        $('#editScore').val($('#score').text());
        $('#comments').addClass('hidden');
        $('#editComments').val($('#comments').html());
        $('#editButton').addClass('hidden');
        tinymceEditor = tinymce.init({ selector: '#editComments', plugins: 'link image code', toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | code', menubar: false });
      });

      $('#saveButton').on('click', function () {
        const updatedScore = $('#editScore').val();
        // console.log("🚀 ~ :370 ~ updatedScore:", updatedScore)
        const updatedComments = tinymce.get('editComments').getContent();
        // console.log("🚀 ~ :372 ~ updatedComments:", updatedComments)
        // console.log("🚀 ~ :384 ~ submissionId:", submissionId)

        $.post('api/update_submission.php', { id: submissionId, score: updatedScore, comments: updatedComments }, function (response) {
          if (response.success) {
            $('#score').text(updatedScore);
            $('#comments').html(updatedComments);
            exitEditMode();
            alert('Submission updated successfully.');
          } else {
            alert(response.message || 'An unknown error occurred.');
          }
        }, 'json');
      });

      $('#cancelButton').on('click', exitEditMode);
      function exitEditMode() {
        $('#editFields').addClass('hidden');
        $('#comments').removeClass('hidden');
        $('#editButton').removeClass('hidden');
        if (tinymceEditor) tinymce.remove('#editComments');
      }

      function fetchInlineComments() {
        $.getJSON('api/fetch_inline_comments.php', { submissionId }, function (res) {
          if (!res.success) {
            return alert(res.message || 'Error fetching comments.');
          }
          const $list = $('#inlineCommentList').empty();
          res.comments.forEach(c => {
            const dt = new Date(c.created_at);
            const ts = dt.toLocaleDateString('en-GB', {
              day: '2-digit', month: 'short', year: 'numeric'
            }) + ' ' + dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            const block = `
          <div class="comment-block">
            <p class="comment-subject">${c.highlighted_text || '…highlighted text…'}</p>
            <p class="comment-body"><strong>Comment:</strong> ${c.comment_text}</p>
            <div class="comment-footer">
              <img src="https://i.pravatar.cc/40?u=${c.user_id || 'anon'}" class="avatar">
              <span class="comment-date">${ts}</span>
            </div>
          </div>`;
            $list.append(block);
          });
        });
      }

      function postInlineComment(highlightedText, commentText) {
        return $.post('api/update_inline_comments.php', {
          submissionId,
          highlightedText,
          commentText
        }, null, 'json');
      }

      // $('#toggleCommentBtn').click(() => {
      //   commentMode = true;
      //   updateUI();
      // });

      $('#toggleCommentBtn').click(() => {
        commentMode = true;
        // fill subtitle from current selection
        const sel = window.getSelection().toString().trim();
        selectedText = sel;
        $('#commentSubtitle').val(sel);
        updateUI();
      });



      // 5b) Cancel comment mode
      $('#cancelCommentBtn').click(() => {
        commentMode = false;
        updateUI();
      });

      function fetchSubmissionDetails(id) {
        $.get('api/fetch_submission.php', { id }, function (response) {
          if (response.success) {
            console.log("🚀 ~ :408 ~ response:", response);

            // $('#submissionFile').attr('src', response.submission.fileName);
            renderPDF(response.submission.fileName);
            $('#fileName').text(response.submission.fileName.split('/').pop());
            $('#studentName').text(response.submission.studentName);
            $('#score').text(response.submission.score);
            $('#comments').html(response.submission.comments);
          } else {
            alert(response.message || 'Error fetching submission details.');
          }
        }, 'json');
      }
      fetchInlineComments();
      updateUI();
    });
  </script>
</body>

</html>