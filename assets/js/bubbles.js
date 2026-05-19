(function() {
  const canvas = document.getElementById('bubbles');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let W, H;

  const COLORS = [
    'rgba(232,160,32,',
    'rgba(224,64,48,',
    'rgba(112,64,192,',
    'rgba(224,96,32,',
    'rgba(208,48,160,',
    'rgba(255,200,80,'
  ];

  function resize() {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }

  window.addEventListener('resize', resize);
  resize();

  class Bubble {
    constructor() {
      this.reset(true);
    }

    reset(initial = false) {
      this.r = Math.random() * 44 + 14;
      this.x = Math.random() * W;
      this.y = initial ? Math.random() * H : H + this.r + 20;
      this.vx = (Math.random() - 0.5) * 0.4;
      this.vy = -(Math.random() * 0.5 + 0.2);
      this.color = COLORS[Math.floor(Math.random() * COLORS.length)];
      this.alpha = Math.random() * 0.14 + 0.06;
      this.pulse = Math.random() * Math.PI * 2;
      this.pulseSpeed = Math.random() * 0.02 + 0.008;
    }

    update() {
      this.x += this.vx;
      this.y += this.vy;
      this.pulse += this.pulseSpeed;
      if (this.y < -this.r - 20) this.reset();
    }

    draw() {
      const a = this.alpha + Math.sin(this.pulse) * 0.03;

      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);

      const g = ctx.createRadialGradient(
        this.x - this.r * 0.3,
        this.y - this.r * 0.3,
        this.r * 0.05,
        this.x,
        this.y,
        this.r
      );

      g.addColorStop(0, this.color + (a + 0.18) + ')');
      g.addColorStop(0.55, this.color + a + ')');
      g.addColorStop(1, this.color + '0)');

      ctx.fillStyle = g;
      ctx.fill();

      ctx.beginPath();
      ctx.arc(
        this.x - this.r * 0.28,
        this.y - this.r * 0.28,
        this.r * 0.15,
        0,
        Math.PI * 2
      );
      ctx.fillStyle = 'rgba(255,255,255,0.55)';
      ctx.fill();

      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.strokeStyle = this.color + (a * 0.5) + ')';
      ctx.lineWidth = 1.5;
      ctx.stroke();
    }
  }

  const bubbles = Array.from({ length: 35 }, () => new Bubble());

  (function loop() {
    ctx.clearRect(0, 0, W, H);
    bubbles.forEach(function(b) {
      b.update();
      b.draw();
    });
    requestAnimationFrame(loop);
  })();
})();