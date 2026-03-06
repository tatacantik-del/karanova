(function(){
  const cards = document.querySelectorAll(".tilt");
  if(!cards.length) return;

  function clamp(n,min,max){ return Math.max(min, Math.min(max,n)); }

  cards.forEach(card=>{
    card.addEventListener("mousemove",(e)=>{
      const r = card.getBoundingClientRect();
      const x = e.clientX - r.left;
      const y = e.clientY - r.top;

      const rotX = clamp(((y / r.height) - 0.5) * -14, -14, 14);
      const rotY = clamp(((x / r.width) - 0.5) * 18, -18, 18);

      card.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) translateY(-6px)`;
      card.style.setProperty("--mx", (x / r.width) * 100 + "%");
      card.style.setProperty("--my", (y / r.height) * 100 + "%");
    });

    card.addEventListener("mouseleave",()=>{
      card.style.transform = "";
      card.style.removeProperty("--mx");
      card.style.removeProperty("--my");
    });
  });
})();