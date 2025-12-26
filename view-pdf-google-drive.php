<style>
.pdf-wrapper {
  position: relative;
  width: 100%;
  height: 600px;
}

.pdf-wrapper iframe {
  width: 100%;
  height: 100%;
}

.iframe-overlay {
  position: absolute;
  top: 0;
  right: 0;
  height: 60px;   /* tinggi toolbar Google */
  width: 50px;
  background : transparent;
  z-index: 10;
}
</style>


<div class="pdf-wrapper">
  <iframe
    src="https://drive.google.com/file/d/1IKY5hNMsT9wfJjSfqsWZMaahQettk3Hq/preview"
    frameborder="0"
    allow="autoplay"
  ></iframe>
  <div class="iframe-overlay"></div>
</div>

<script>
 //block click kanan
document.addEventListener('contextmenu', e => e.preventDefault());

//block shortcut
document.addEventListener('keydown', function(e) {
    if (
        e.key === 'F12' ||
        (e.ctrlKey && e.shiftKey && ['i','j','c'].includes(e.key.toLowerCase())) ||
        (e.ctrlKey && e.key.toLowerCase() === 'u')
    ) {
        e.preventDefault();
    }
});

setInterval(() => {
    if (window.outerWidth - window.innerWidth > 160 ||
        window.outerHeight - window.innerHeight > 160) {
        document.body.innerHTML = "<h2>DevTools terdeteksi</h2>";
    }
}, 1000);
</script>