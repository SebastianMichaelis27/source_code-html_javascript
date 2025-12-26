<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Akses Kamera</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
  font-family: Arial, sans-serif;
  text-align: center;
  padding: 20px;
}
video {
  width: 100%;
  max-width: 360px;
  border-radius: 8px;
  border: 1px solid #ccc;
}
button, select {
  padding: 10px;
  margin: 5px;
  font-size: 14px;
}
#blocked {
  display: none;
  background: #ffe6e6;
  color: #a00;
  padding: 15px;
  border-radius: 6px;
  margin-top: 15px;
}
canvas, img {
  display: none;
}
</style>
</head>

<body>

<h3>Akses Kamera</h3>

<select id="cameraSelect"></select><br>

<video id="video" autoplay playsinline></video><br>

<button onclick="startCamera()">Aktifkan Kamera</button>
<button onclick="capture()">Ambil Foto</button>

<div id="blocked">
  <strong>Kamera diblokir!</strong>
  <p>
    Klik icon <b>🔒 / 🎥</b> di address bar,<br>
    ubah <b>Camera → Allow</b>, lalu refresh halaman.
  </p>
  <button onclick="location.reload()">Coba Lagi</button>
</div>

<canvas id="canvas"></canvas>
<br>
<img id="photo">

<script>
const video = document.getElementById("video");
const cameraSelect = document.getElementById("cameraSelect");
const blockedBox = document.getElementById("blocked");
const canvas = document.getElementById("canvas");
const photo = document.getElementById("photo");

let stream = null;

// stop kamera
function stopCamera() {
  if (stream) {
    stream.getTracks().forEach(t => t.stop());
    stream = null;
  }
}

// start kamera
async function startCamera(deviceId = null) {
  stopCamera();
  blockedBox.style.display = "none";

  try {
    const constraints = {
      video: deviceId
        ? { deviceId: { exact: deviceId } }
        : { facingMode: "environment" }
    };

    stream = await navigator.mediaDevices.getUserMedia(constraints);
    video.srcObject = stream;

  } catch (err) {
    if (err.name === "NotAllowedError") {
      blockedBox.style.display = "block";
    } else {
      alert("Error kamera: " + err.message);
    }
  }
}

// ambil foto
function capture() {
  if (!stream) {
    alert("Kamera belum aktif");
    return;
  }

  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;

  const ctx = canvas.getContext("2d");
  ctx.drawImage(video, 0, 0);

  photo.src = canvas.toDataURL("image/png");
  photo.style.display = "block";
}

// load daftar kamera
async function loadCameras() {
  const devices = await navigator.mediaDevices.enumerateDevices();
  const cams = devices.filter(d => d.kind === "videoinput");

  cameraSelect.innerHTML = "";
  cams.forEach((cam, i) => {
    const opt = document.createElement("option");
    opt.value = cam.deviceId;
    opt.text = cam.label || "Camera " + (i + 1);
    //opt.text = cam.label || `Camera ${cameraSelect.length + 1}`;
    cameraSelect.appendChild(opt);
  });

  if (cams.length > 0) {
    startCamera(cams[0].deviceId);
  }
}

cameraSelect.onchange = () => {
  startCamera(cameraSelect.value);
};

// cek status permission saat load
navigator.permissions?.query({ name: "camera" }).then(p => {
  if (p.state === "denied") {
    blockedBox.style.display = "block";
  }
});

loadCameras();
</script>

</body>
</html>