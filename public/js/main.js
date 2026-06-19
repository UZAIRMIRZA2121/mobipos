document.addEventListener("DOMContentLoaded", () => {
    // Initialize AOS Animation
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100,
    });

    // Swiper Testimonials Initialization
    const swiper = new Swiper('.testimonials-swiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            }
        }
    });

    // GSAP ScrollTrigger for Stats Counters
    gsap.registerPlugin(ScrollTrigger);

    const counters = document.querySelectorAll('.counter-val');
    counters.forEach(counter => {
        let target = parseInt(counter.getAttribute('data-target'));
        let isPlus = counter.getAttribute('data-target').includes('+') || counter.innerHTML.includes('+');
        let isPercent = counter.getAttribute('data-target').includes('%') || counter.innerHTML.includes('%');
        let prefix = "";
        let suffix = "";
        if(isPlus) suffix = "+";
        if(isPercent) suffix = "%";

        ScrollTrigger.create({
            trigger: counter,
            start: "top 80%",
            once: true,
            onEnter: () => {
                gsap.to(counter, {
                    innerHTML: target,
                    duration: 2,
                    snap: { innerHTML: 1 },
                    onUpdate: function() {
                        // Formatting the number
                        let val = Math.round(this.targets()[0].innerHTML);
                        counter.innerHTML = val.toLocaleString() + suffix;
                    }
                });
            }
        });
    });

    // GSAP Mockup Parallax
    gsap.to(".mockup-left", {
        y: -50,
        ease: "none",
        scrollTrigger: {
            trigger: ".mockup-container",
            scrub: true
        }
    });
    
    gsap.to(".mockup-right", {
        y: 50,
        ease: "none",
        scrollTrigger: {
            trigger: ".mockup-container",
            scrub: true
        }
    });

    // Mouse Movement Parallax on floating items
    document.addEventListener("mousemove", (e) => {
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;

        gsap.to(".mockup-main", {
            x: x * 2,
            y: y * 2,
            duration: 1
        });
    });
});
