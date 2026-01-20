/**
 * Categories Toggle Logic
 * Handles the opening and closing of the "All Categories" dropdown panel.
 */
const toggle = document.getElementById("categoriesToggle");
const panel = document.getElementById("categoriesPanel");

if (toggle && panel) {
  // Function to set the open state (true/false)
  const setOpen = (open) => {
    // aria-hidden="true" means hidden (closed)
    panel.setAttribute("aria-hidden", String(!open));
    toggle.setAttribute("aria-expanded", String(open));
    panel.classList.toggle("show", open);
  };

  // Initialize as closed
  setOpen(false);

  // Toggle on button click
  toggle.addEventListener("click", () => {
    const isHidden = panel.getAttribute("aria-hidden") === "true";
    setOpen(isHidden); // If hidden, we open it
  });

  // Close when clicking outside
  document.addEventListener("click", (e) => {
    if (!panel.contains(e.target) && !toggle.contains(e.target)) {
      setOpen(false);
    }
  });

  // Close on Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setOpen(false);
  });
}

/**
 * Carousel Logic
 * Handles the auto-sliding hero banner and manual navigation.
 */
const carousel = document.getElementById("carousel");
if (carousel) {
  const slides = Array.from(carousel.querySelectorAll(".slide"));
  const prevBtn = carousel.querySelector(".carousel-control.prev");
  const nextBtn = carousel.querySelector(".carousel-control.next");
  const indicatorsEl = document.getElementById("indicators");
  let index = 0;
  let timer = null;

  // Render clickable dots at the bottom
  const renderIndicators = () => {
    indicatorsEl.innerHTML = "";
    slides.forEach((_, i) => {
      const b = document.createElement("button");
      if (i === index) b.classList.add("active");
      b.addEventListener("click", () => goTo(i));
      indicatorsEl.appendChild(b);
    });
  };

  // Switch to specific slide index
  const goTo = (i) => {
    // Wrap around logic
    index = (i + slides.length) % slides.length;
    slides.forEach((s, j) => s.classList.toggle("active", j === index));
    renderIndicators();
  };

  const next = () => goTo(index + 1);
  const prev = () => goTo(index - 1);

  // Auto-play functionality
  const start = () => {
    if (timer) clearInterval(timer);
    timer = setInterval(next, 5000); // Change slide every 5s
  };
  const stop = () => timer && clearInterval(timer);

  // Event listeners for controls
  if (prevBtn) prevBtn.addEventListener("click", prev);
  if (nextBtn) nextBtn.addEventListener("click", next);

  // Pause on hover
  carousel.addEventListener("mouseenter", stop);
  carousel.addEventListener("mouseleave", start);

  // Initialize
  goTo(0);
  start();
}

/**
 * Categories Animation
 * Uses IntersectionObserver to trigger fade-in animation
 * when the section scrolls into view.
 */
const categoriesSection = document.getElementById("ourCategories");

if (categoriesSection) {
  const items = categoriesSection.querySelectorAll(".category-item");

  // Configure observer to trigger when 15% of section is visible
  const observerOptions = {
    root: null, // viewport
    rootMargin: "0px",
    threshold: 0.15,
  };

  let timeouts = [];

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        // Animate items one by one with a stagger delay
        items.forEach((item, index) => {
          const t = setTimeout(() => {
            item.classList.add("visible");
          }, index * 100); // 100ms delay between each item
          timeouts.push(t);
        });
      } else {
        // Reset animation when out of view
        // Clear pending timeouts to prevent items appearing after scrolling away
        timeouts.forEach(clearTimeout);
        timeouts = [];

        items.forEach((item) => {
          item.classList.remove("visible");
        });
      }
    });
  }, observerOptions);

  observer.observe(categoriesSection);
}

/**
 * Trusted Brands Animation
 * Handles the slide-in for text and fade-in for logos.
 */
const brandsSection = document.getElementById("trustedBrands");

if (brandsSection) {
  const text = brandsSection.querySelector(".brands-text");
  const logos = brandsSection.querySelectorAll(".brand-item");

  const brandObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // Fade in text from left
          if (text) text.classList.add("visible");

          // Logos appear (simple fade in, all at once or fast stagger)
          logos.forEach((logo, index) => {
            setTimeout(() => {
              logo.classList.add("visible");
            }, index * 50); // Very fast stagger
          });
        } else {
          // Optional: Reset if we want them to animate every time we scroll back
          if (text) text.classList.remove("visible");
          logos.forEach((logo) => logo.classList.remove("visible"));
        }
      });
    },
    { threshold: 0.2 }
  );

  brandObserver.observe(brandsSection);
}

/**
 * New Arrivals Animation
 * Staggered fade-in from bottom.
 */
const newArrivalsSection = document.getElementById("newArrivals");

if (newArrivalsSection) {
  const cards = newArrivalsSection.querySelectorAll(".product-card");

  const productObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          cards.forEach((card, index) => {
            setTimeout(() => {
              card.classList.add("visible");
            }, index * 100);
          });
        } else {
          cards.forEach((card) => card.classList.remove("visible"));
        }
      });
    },
    { threshold: 0.1 }
  );

  productObserver.observe(newArrivalsSection);
}

const flashSaleSection = document.getElementById("flashSale");
if (flashSaleSection) {
  const numbers = {
    days: flashSaleSection.querySelector('.number[data-unit="days"]'),
    hours: flashSaleSection.querySelector('.number[data-unit="hours"]'),
    minutes: flashSaleSection.querySelector('.number[data-unit="minutes"]'),
    seconds: flashSaleSection.querySelector('.number[data-unit="seconds"]'),
  };

  const end = new Date(
    Date.now() + 2 * 24 * 60 * 60 * 1000 + 5 * 60 * 60 * 1000
  );
  let intervalId = null;

  const update = () => {
    const now = new Date();
    let diff = Math.max(0, end - now);

    const d = Math.floor(diff / (24 * 60 * 60 * 1000));
    diff -= d * 24 * 60 * 60 * 1000;

    const h = Math.floor(diff / (60 * 60 * 1000));
    diff -= h * 60 * 60 * 1000;

    const m = Math.floor(diff / (60 * 1000));
    diff -= m * 60 * 1000;

    const s = Math.floor(diff / 1000);

    if (numbers.days) numbers.days.textContent = d;
    if (numbers.hours) numbers.hours.textContent = h;
    if (numbers.minutes) numbers.minutes.textContent = m;
    if (numbers.seconds) numbers.seconds.textContent = s;

    if (d === 0 && h === 0 && m === 0 && s === 0) {
      clearInterval(intervalId);
      intervalId = null;
    }
  };

  const start = () => {
    if (intervalId) return;
    update();
    intervalId = setInterval(update, 1000);
  };
  const stop = () => {
    if (intervalId) {
      clearInterval(intervalId);
      intervalId = null;
    }
  };

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) start();
        else stop();
      });
    },
    { threshold: 0.2 }
  );
  observer.observe(flashSaleSection);
}

/**
 * Best Sell Animation
 * Fade in from right on enter, reset on exit.
 */
const bestSellSection = document.getElementById("bestSell");
if (bestSellSection) {
  const cards = bestSellSection.querySelectorAll(".product-card");
  const observerOptions = { root: null, rootMargin: "0px", threshold: 0.1 };
  let timeouts = [];

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        cards.forEach((card, index) => {
          const t = setTimeout(() => {
            card.classList.add("visible");
          }, index * 120);
          timeouts.push(t);
        });
      } else {
        timeouts.forEach(clearTimeout);
        timeouts = [];
        cards.forEach((card) => {
          card.classList.remove("visible");
        });
      }
    });
  }, observerOptions);

  observer.observe(bestSellSection);
}

const topSellingSection = document.getElementById("topSelling");
if (topSellingSection) {
  const cards = topSellingSection.querySelectorAll(".product-card");
  const productObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          cards.forEach((card, index) => {
            setTimeout(() => {
              card.classList.add("visible");
            }, index * 100);
          });
        } else {
          cards.forEach((card) => card.classList.remove("visible"));
        }
      });
    },
    { threshold: 0.1 }
  );
  productObserver.observe(topSellingSection);
}

if (document.body.classList.contains("about-page")) {
  const scoreEl = document.querySelector(".big-score .score");
  const basedEl = document.querySelector(".big-score .based-on");
  if (scoreEl && basedEl) {
    let score = 4.9;
    let dir = 1;
    let reviews = 5000;
    const loop = () => {
      score += 0.01 * dir;
      if (score >= 5.0) dir = -1;
      if (score <= 4.7) dir = 1;
      scoreEl.textContent = score.toFixed(1);
      reviews++;
      if (reviews > 9999) reviews = 5000;
      basedEl.textContent =
        "Based on " + reviews.toLocaleString() + "+ reviews";
      requestAnimationFrame(loop);
    };
    loop();
  }
}

if (document.body.classList.contains("shop-list-page")) {
  const grid = document.querySelector(".products-grid");
  if (grid) {
    const cards = grid.querySelectorAll(".product-card");
    cards.forEach((card, index) => {
      setTimeout(() => card.classList.add("visible"), index * 100);
    });
  }
}
if (document.body.classList.contains("faq-page")) {
  const list = document.querySelector(".faq-list");
  if (list) {
    const sync = () => {
      document.querySelectorAll(".faq-item").forEach((item) => {
        const t = item.querySelector(".faq-toggle");
        if (t) t.textContent = item.classList.contains("open") ? "−" : "+";
      });
    };
    sync();
    list.addEventListener("click", (e) => {
      const t = e.target;
      if (!(t instanceof Element)) return;
      const targetToggle = t.closest(".faq-toggle");
      const targetQuestion = t.closest(".faq-question");
      if (!targetToggle && !targetQuestion) return;
      e.preventDefault();
      const item = t.closest(".faq-item");
      if (!item) return;
      const opened = item.classList.contains("open");
      document
        .querySelectorAll(".faq-item.open")
        .forEach((el) => el.classList.remove("open"));
      if (!opened) item.classList.add("open");
      sync();
    });
  }
}
