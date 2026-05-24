import { useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import HeroFilm from '../components/HeroFilm'
import AuthCard from '../components/AuthCard'
import Navigation from '../components/Navigation'
import {
  BookOpen,
  BarChart3,
  Brain,
  Users,
  Shield,
  Zap,
  ChevronRight,
  GraduationCap,
} from 'lucide-react'

gsap.registerPlugin(ScrollTrigger)

const features = [
  {
    icon: BookOpen,
    title: 'Smart Course Management',
    description:
      'Access all your course materials, lecture notes, and assignments in one organized space. Never lose track of what matters.',
  },
  {
    icon: BarChart3,
    title: 'Real-Time Analytics',
    description:
      'Track your academic progress with detailed insights. Understand your strengths and identify areas for improvement.',
  },
  {
    icon: Brain,
    title: 'AI Study Assistant',
    description:
      'Get personalized study recommendations, explanations, and guidance tailored to your learning style and pace.',
  },
  {
    icon: Users,
    title: 'Collaborative Learning',
    description:
      'Connect with classmates, join study groups, and share resources. Learning is better together.',
  },
  {
    icon: Shield,
    title: 'Attendance Tracking',
    description:
      'Monitor your attendance in real-time. Get alerts before you hit critical thresholds.',
  },
  {
    icon: Zap,
    title: 'Instant Notifications',
    description:
      'Stay informed about deadlines, announcements, and grades. Never miss what matters most.',
  },
]

const stats = [
  { value: '11', label: 'Departments', suffix: '' },
  { value: '50+', label: 'Expert Lecturers', suffix: '' },
  { value: '2,000+', label: 'Students', suffix: '' },
  { value: '100%', label: 'Digital Learning', suffix: '' },
]

const departments = [
  'Computer Science',
  'Science Laboratory Technology',
  'Animal Health',
  'Animal Production',
  'Statistics',
  'Veterinary',
  'Biology',
  'Microbiology',
  'Physics',
  'Agricultural Extension',
  'Fishery',
]

export default function HomePage() {
  const navigate = useNavigate()
  const featuresRef = useRef<HTMLDivElement>(null)
  const statsRef = useRef<HTMLDivElement>(null)
  const deptRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const triggers: ScrollTrigger[] = []

    // Features animation
    if (featuresRef.current) {
      const cards = featuresRef.current.querySelectorAll('.feature-card')
      cards.forEach((card, index) => {
        const tween = gsap.fromTo(
          card,
          { opacity: 0, y: 40 },
          {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: 'power2.out',
            scrollTrigger: {
              trigger: card,
              start: 'top 85%',
              toggleActions: 'play none none none',
            },
            delay: index * 0.1,
          }
        )
        if (tween.scrollTrigger) triggers.push(tween.scrollTrigger)
      })
    }

    // Stats animation
    if (statsRef.current) {
      const items = statsRef.current.querySelectorAll('.stat-item')
      items.forEach((item, index) => {
        const tween = gsap.fromTo(
          item,
          { opacity: 0, scale: 0.8 },
          {
            opacity: 1,
            scale: 1,
            duration: 0.5,
            ease: 'back.out(1.7)',
            scrollTrigger: {
              trigger: item,
              start: 'top 85%',
              toggleActions: 'play none none none',
            },
            delay: index * 0.1,
          }
        )
        if (tween.scrollTrigger) triggers.push(tween.scrollTrigger)
      })
    }

    return () => {
      triggers.forEach((t) => t.kill())
    }
  }, [])

  return (
    <div className="min-h-screen bg-white">
      <Navigation />

      {/* Hero Section */}
      <section className="relative lg:flex">
        {/* Hero Film - Left on desktop */}
        <div className="lg:w-[58%] xl:w-[60%]">
          <HeroFilm />
        </div>

        {/* Auth Card - Right on desktop, sticky */}
        <div
          className="lg:w-[42%] xl:w-[40%] lg:sticky lg:top-0 lg:h-screen flex items-center justify-center px-4 md:px-8 py-12"
          style={{ backgroundColor: '#F5F5F7' }}
        >
          <AuthCard />
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 md:py-24 bg-white" ref={statsRef}>
        <div className="max-w-6xl mx-auto px-4 md:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12">
            {stats.map((stat, index) => (
              <div key={index} className="stat-item text-center">
                <div className="text-4xl md:text-5xl font-bold text-black mb-2">
                  {stat.value}
                  <span style={{ color: '#D10000' }}>{stat.suffix}</span>
                </div>
                <div className="text-sm text-gray-500 font-medium">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16 md:py-24" style={{ backgroundColor: '#F5F5F7' }}>
        <div className="max-w-6xl mx-auto px-4 md:px-8">
          <div className="text-center mb-12 md:mb-16">
            <span
              className="inline-block text-xs font-mono-data font-semibold tracking-[0.2em] uppercase mb-4 px-4 py-1.5 rounded-full"
              style={{ backgroundColor: 'rgba(209, 0, 0, 0.08)', color: '#D10000' }}
            >
              Platform Features
            </span>
            <h2 className="text-3xl md:text-4xl font-bold text-black mb-4">
              Everything you need to
              <span style={{ color: '#D10000' }}> succeed</span>
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              Built from the ground up around the student experience. No more fragmented tools, 
              no more missed deadlines. Just clarity.
            </p>
          </div>

          <div ref={featuresRef} className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {features.map((feature, index) => (
              <div
                key={index}
                className="feature-card card-elevated p-6 md:p-8 group cursor-pointer"
              >
                <div
                  className="w-12 h-12 rounded-xl flex items-center justify-center mb-5 transition-transform group-hover:scale-110"
                  style={{ backgroundColor: 'rgba(209, 0, 0, 0.08)' }}
                >
                  <feature.icon className="w-6 h-6" style={{ color: '#D10000' }} />
                </div>
                <h3 className="text-lg font-semibold text-black mb-2">{feature.title}</h3>
                <p className="text-sm text-gray-600 leading-relaxed">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Departments Section */}
      <section className="py-16 md:py-24 bg-white" ref={deptRef}>
        <div className="max-w-6xl mx-auto px-4 md:px-8">
          <div className="flex flex-col md:flex-row md:items-end md:justify-between mb-10">
            <div>
              <span
                className="inline-block text-xs font-mono-data font-semibold tracking-[0.2em] uppercase mb-4 px-4 py-1.5 rounded-full"
                style={{ backgroundColor: 'rgba(209, 0, 0, 0.08)', color: '#D10000' }}
              >
                Departments
              </span>
              <h2 className="text-3xl md:text-4xl font-bold text-black">
                FCAHPT <span style={{ color: '#D10000' }}>Ibadan</span>
              </h2>
            </div>
            <p className="text-gray-600 mt-4 md:mt-0 max-w-md">
              Eleven departments offering National Diploma (ND) and Higher National Diploma (HND) 
              programs across the sciences.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {departments.map((dept, index) => (
              <div
                key={index}
                className="flex items-center gap-4 p-4 rounded-2xl border border-gray-100 hover:border-[#D10000]/20 hover:shadow-md transition-all duration-200 group cursor-pointer"
              >
                <div
                  className="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors group-hover:bg-[#D10000]"
                  style={{ backgroundColor: '#F5F5F7' }}
                >
                  <GraduationCap className="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" />
                </div>
                <div className="flex-1 min-w-0">
                  <h4 className="text-sm font-semibold text-black truncate">{dept}</h4>
                  <p className="text-xs text-gray-500">
                    {index < 9 ? 'ND & HND Programs' : 'ND Program'}
                  </p>
                </div>
                <ChevronRight className="w-4 h-4 text-gray-300 group-hover:text-[#D10000] transition-colors flex-shrink-0" />
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16 md:py-24 bg-black relative overflow-hidden">
        <div
          className="absolute inset-0 opacity-30"
          style={{
            backgroundImage: 'url(/images/scene7-ecosystem.jpg)',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-r from-black via-black/90 to-black/70" />
        <div className="max-w-4xl mx-auto px-4 md:px-8 text-center relative z-10">
          <h2 className="text-3xl md:text-5xl font-bold text-white mb-6 hero-text-cinematic">
            Ready to take control of your academic journey?
          </h2>
          <p className="text-lg text-gray-400 mb-8 max-w-2xl mx-auto">
            Join thousands of FCAHPT Ibadan students who are already using ALMS to 
            stay organized, focused, and ahead.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              onClick={() => navigate('/role')}
              className="btn-primary text-base py-3 px-8"
            >
              Get Started for Free
              <ChevronRight className="w-5 h-5" />
            </button>
            <button
              onClick={() => navigate('/courses')}
              className="btn-outline text-base py-3 px-8 border-white/30 text-white hover:bg-white hover:text-black"
            >
              Explore Courses
            </button>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12 bg-white border-t border-gray-100">
        <div className="max-w-6xl mx-auto px-4 md:px-8">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="flex items-center gap-3">
              <div
                className="w-8 h-8 rounded-lg flex items-center justify-center"
                style={{ backgroundColor: '#D10000' }}
              >
                <GraduationCap className="w-4 h-4 text-white" />
              </div>
              <div>
                <span className="text-sm font-bold text-black">FCAHPT IBADAN</span>
                <span className="block text-[9px] font-mono-data text-gray-500 tracking-widest uppercase">
                  Academic Learning Management System
                </span>
              </div>
            </div>
            <div className="flex items-center gap-6 text-sm text-gray-500">
              <button className="hover:text-[#D10000] transition-colors">Privacy</button>
              <button className="hover:text-[#D10000] transition-colors">Terms</button>
              <button className="hover:text-[#D10000] transition-colors">Support</button>
            </div>
            <p className="text-xs text-gray-400">
              &copy; {new Date().getFullYear()} ALMS FCAHPT Ibadan. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  )
}
