const canvas = document.getElementById('rippleCanvas');
const ctx = canvas.getContext('2d');

let width = canvas.width = canvas.offsetWidth;
let height = canvas.height = canvas.offsetHeight;

let mouse = { x: width / 2, y: height / 2 };
let bg = new Image();
bg.src = './images/stars-bg.jpg'; // Use your preferred background image

bg.onload = () => requestAnimationFrame(draw);

function resize() {
    width = canvas.width = canvas.offsetWidth;
    height = canvas.height = canvas.offsetHeight;
}
window.addEventListener('resize', resize);
window.addEventListener('mousemove', e => {
    const rect = canvas.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
});

function draw() {
    ctx.clearRect(0, 0, width, height);
    ctx.drawImage(bg, 0, 0, width, height);

    const r = 100;
    const grad = ctx.createRadialGradient(mouse.x, mouse.y, 0, mouse.x, mouse.y, r);
    grad.addColorStop(0, 'rgba(255,255,255,0.2)');
    grad.addColorStop(1, 'rgba(255,255,255,0)');

    ctx.fillStyle = grad;
    ctx.fillRect(mouse.x - r, mouse.y - r, r * 2, r * 2);

    requestAnimationFrame(draw);
}
