import { useEffect, useRef } from 'react'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const scenes = [
  {
    image: '/images/scene1-weight.jpg',
    text: "Learning shouldn't feel this overwhelming.",
    subtext: null,
  },
  {
    image: '/images/scene2-pressure.jpg',
    text: "Too much pressure.",
    subtext: "Too little guidance.",
  },
  {
    image: '/images/scene3-breakpoint.jpg',
    text: "Students are not failing because they are incapable.",
    subtext: "They are struggling because learning systems were never built around them.",
  },
  {
    image: '/images/scene4-pivot.jpg',
    text: "Until now.",
    subtext: null,
  },
  {
    image: '/images/scene5-architecture.jpg',
    text: "Academic tools. Intuitive structure.",
    subtext: "Designed for how you actually learn.",
  },
  {
    image: '/images/scene6-flow.jpg',
    text: "Submit with confidence.",
    subtext: "Track with clarity.",
  },
  {
    image: '/images/scene7-ecosystem.jpg',
    text: "A network built for the",
    subtext: "Federal College of Animal Health \u0026 Production Technology.",
  },
  {
    image: '/images/scene8-climax.jpg',
    text: "Welcome to your",
    subtext: "academic command center.",
  },
]

export default function HeroFilm() {
  const containerRef = useRef<HTMLDivElement>(null)
  const stickyRef = useRef<HTMLDivElement>(null)
  const imageRefs = useRef<(HTMLDivElement | null)[]>([])
  const textRefs = useRef<(HTMLDivElement | null)[]>([])
  const progressRef = useRef<HTMLDivElement>(null)
  const dustCanvasRef = useRef<HTMLCanvasElement>(null)

  // Dust particles animation
  useEffect(() => {
    const canvas = dustCanvasRef.current
    if (!canvas) return

    const ctx = canvas.getContext('2d')
    if (!ctx) return

    let animId: number
    const particles: { x: number; y: number; vx: number; vy: number; size: number; opacity: number }[] = []

    const resize = () => {
      canvas.width = canvas.offsetWidth * window.devicePixelRatio
      canvas.height = canvas.offsetHeight * window.devicePixelRatio
      ctx.scale(window.devicePixelRatio, window.devicePixelRatio)
    }
    resize()
    window.addEventListener('resize', resize)

    for (let i = 0; i < 60; i++) {
      particles.push({
        x: Math.random() * canvas.offsetWidth,
        y: Math.random() * canvas.offsetHeight,
        vx: (Math.random() - 0.5) * 0.3,
        vy: -Math.random() * 0.4 - 0.1,
        size: Math.random() * 2 + 0.5,
        opacity: Math.random() * 0.5 + 0.1,
      })
    }

    const animate = () => {
      ctx.clearRect(0, 0, canvas.offsetWidth, canvas.offsetHeight)
      particles.forEach((p) => {
        p.x += p.vx
        p.y += p.vy
        if (p.y < -10) {
          p.y = canvas.offsetHeight + 10
          p.x = Math.random() * canvas.offsetWidth
        }
        if (p.x < -10) p.x = canvas.offsetWidth + 10
        if (p.x > canvas.offsetWidth + 10) p.x = -10

        ctx.beginPath()
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2)
        ctx.fillStyle = `rgba(255, 255, 255, ${p.opacity})`
        ctx.fill()
      })
      animId = requestAnimationFrame(animate)
    }
    animate()

    return () => {
      cancelAnimationFrame(animId)
      window.removeEventListener('resize', resize)
    }
  }, [])

  // GSAP ScrollTrigger scene transitions
  useEffect(() => {
    const container = containerRef.current
    if (!container) return

    const triggers: ScrollTrigger[] = []

    // Main scroll trigger for the sticky section
    const mainTrigger = ScrollTrigger.create({
      trigger: container,
      start: 'top top',
      end: 'bottom bottom',
      onUpdate: (self) => {
        if (progressRef.current) {
          progressRef.current.style.height = `${self.progress * 100}%`
        }
      },
    })
    triggers.push(mainTrigger)

    // Scene transitions
    scenes.forEach((_, index) => {
      const imageEl = imageRefs.current[index]
      const textEl = textRefs.current[index]
      if (!imageEl || !textEl) return

      const sceneStart = index / scenes.length
      const sceneEnd = (index + 1) / scenes.length

      // Image crossfade
      const imgTrigger = ScrollTrigger.create({
        trigger: container,
        start: `${sceneStart * 100}% top`,
        end: `${sceneEnd * 100}% top`,
        scrub: 0.8,
        onUpdate: (self) => {
          const progress = self.progress
          // Fade in during first 20%, stay, fade out during last 20%
          let opacity = 1
          if (progress < 0.2) {
            opacity = progress / 0.2
          } else if (progress > 0.8) {
            opacity = (1 - progress) / 0.2
          }
          imageEl.style.opacity = String(Math.max(0, Math.min(1, opacity)))

          // Subtle scale for depth
          const scale = 1 + (progress * 0.05)
          imageEl.style.transform = `scale(${scale})`
        },
      })
      triggers.push(imgTrigger)

      // Text blur-to-focus reveal
      const textLines = textEl.querySelectorAll('.scene-line')
      textLines.forEach((line, lineIndex) => {
        const lineTrigger = ScrollTrigger.create({
          trigger: container,
          start: `${(sceneStart + 0.15 + lineIndex * 0.05) * 100}% top`,
          end: `${(sceneStart + 0.4 + lineIndex * 0.05) * 100}% top`,
          scrub: 0.5,
          onUpdate: (self) => {
            const p = self.progress
            const el = line as HTMLElement
            el.style.opacity = String(Math.min(1, p * 2))
            el.style.filter = `blur(${(1 - p) * 12}px)`
            el.style.transform = `translateY(${(1 - p) * 30}px)`
          },
        })
        triggers.push(lineTrigger)
      })
    })

    return () => {
      triggers.forEach((t) => t.kill())
    }
  }, [])

  return (
    <div ref={containerRef} className="relative" style={{ height: `${scenes.length * 100}vh` }}>
      {/* Sticky viewport */}
      <div
        ref={stickyRef}
        className="sticky top-0 w-full overflow-hidden film-grain"
        style={{ height: '100vh' }}
      >
        {/* Background image layers */}
        {scenes.map((scene, index) => (
          <div
            key={index}
            ref={(el) => { imageRefs.current[index] = el }}
            className="absolute inset-0 will-change-transform"
            style={{
              opacity: index === 0 ? 1 : 0,
              zIndex: 1,
            }}
          >
            <img
              src={scene.image}
              alt={`Scene ${index + 1}`}
              className="w-full h-full object-cover"
              loading={index < 2 ? 'eager' : 'lazy'}
            />
            {/* Dark overlay for mood */}
            <div
              className="absolute inset-0"
              style={{
                background:
                  index < 4
                    ? 'linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.5) 100%)'
                    : 'linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.3) 100%)',
              }}
            />
          </div>
        ))}

        {/* Dust particles canvas */}
        <canvas
          ref={dustCanvasRef}
          className="absolute inset-0 pointer-events-none"
          style={{ zIndex: 3 }}
        />

        {/* Text overlays */}
        {scenes.map((scene, index) => (
          <div
            key={index}
            ref={(el) => { textRefs.current[index] = el }}
            className="absolute inset-0 flex flex-col items-center justify-center px-8 md:px-16 pointer-events-none"
            style={{ zIndex: 4, opacity: 0 }}
          >
            <div className="max-w-4xl text-center">
              <h2
                className="scene-line hero-text-cinematic text-white font-bold leading-tight"
                style={{
                  fontSize: 'clamp(1.8rem, 5vw, 4rem)',
                  textShadow: '0 4px 32px rgba(0,0,0,0.6)',
                  opacity: 0,
                }}
              >
                {scene.text}
              </h2>
              {scene.subtext && (
                <p
                  className="scene-line hero-text-cinematic text-white/90 font-medium mt-4 md:mt-6"
                  style={{
                    fontSize: 'clamp(1.1rem, 2.5vw, 1.8rem)',
                    textShadow: '0 2px 16px rgba(0,0,0,0.5)',
                    opacity: 0,
                  }}
                >
                  {scene.subtext}
                </p>
              )}
            </div>
          </div>
        ))}

        {/* Scroll progress indicator */}
        <div
          className="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-[2px] h-32 bg-white/20 rounded-full overflow-hidden"
          style={{ zIndex: 5 }}
        >
          <div
            ref={progressRef}
            className="w-full bg-white/80 rounded-full transition-none"
            style={{ height: '0%' }}
          />
        </div>

        {/* Scroll hint */}
        <div
          className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2"
          style={{ zIndex: 5 }}
        >
          <span className="text-white/60 text-xs font-mono-data tracking-widest uppercase">
            Scroll to experience
          </span>
          <div className="w-[1px] h-8 bg-gradient-to-b from-white/60 to-transparent animate-pulse" />
        </div>
      </div>
    </div>
  )
}
