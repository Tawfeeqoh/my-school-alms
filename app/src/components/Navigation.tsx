import { useState, useEffect } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import { Menu, X, GraduationCap } from 'lucide-react'

export default function Navigation() {
  const navigate = useNavigate()
  const location = useLocation()
  const [scrolled, setScrolled] = useState(false)
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50)
    }
    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  const isHome = location.pathname === '/'

  return (
    <header
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled || !isHome
          ? 'bg-white/90 backdrop-blur-xl shadow-sm'
          : 'bg-transparent'
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 md:px-8">
        <div className="flex items-center justify-between h-16 md:h-20">
          {/* Logo */}
          <button
            onClick={() => navigate('/')}
            className="flex items-center gap-3 group"
          >
            <div
              className="w-9 h-9 rounded-lg flex items-center justify-center transition-transform group-hover:scale-105"
              style={{ backgroundColor: '#D10000' }}
            >
              <GraduationCap className="w-5 h-5 text-white" />
            </div>
            <div className="hidden sm:block">
              <span
                className={`text-sm font-bold tracking-tight transition-colors ${
                  scrolled || !isHome ? 'text-black' : 'text-white'
                }`}
              >
                FCAHPT IBADAN
              </span>
              <span
                className={`block text-[9px] font-mono-data tracking-[0.2em] uppercase transition-colors ${
                  scrolled || !isHome ? 'text-gray-500' : 'text-white/70'
                }`}
              >
                Academic LMS
              </span>
            </div>
          </button>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-8">
            {['Home', 'Courses', 'Resources', 'Contact'].map((item) => (
              <button
                key={item}
                onClick={() => {
                  if (item === 'Home') navigate('/')
                  else if (item === 'Courses') navigate('/courses')
                  else navigate(item.toLowerCase())
                }}
                className={`nav-link ${
                  scrolled || !isHome ? '' : 'text-white after:bg-white'
                }`}
              >
                {item}
              </button>
            ))}
          </nav>

          {/* Desktop Actions */}
          <div className="hidden md:flex items-center gap-4">
            <button
              onClick={() => navigate('/role')}
              className={`text-sm font-medium transition-colors ${
                scrolled || !isHome
                  ? 'text-black hover:text-[#D10000]'
                  : 'text-white hover:text-white/80'
              }`}
            >
              Log In
            </button>
            <button
              onClick={() => navigate('/role')}
              className="btn-primary text-sm py-2.5 px-6"
            >
              Get Started
            </button>
          </div>

          {/* Mobile Menu Toggle */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className={`md:hidden p-2 rounded-lg transition-colors ${
              scrolled || !isHome ? 'text-black' : 'text-white'
            }`}
          >
            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-white/95 backdrop-blur-xl border-t border-gray-100">
          <div className="px-4 py-4 space-y-2">
            {['Home', 'Courses', 'Resources', 'Contact'].map((item) => (
              <button
                key={item}
                onClick={() => {
                  setMobileMenuOpen(false)
                  if (item === 'Home') navigate('/')
                  else if (item === 'Courses') navigate('/courses')
                }}
                className="block w-full text-left px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-[#D10000] transition-colors"
              >
                {item}
              </button>
            ))}
            <div className="pt-2 border-t border-gray-100 flex gap-3 px-4">
              <button
                onClick={() => {
                  setMobileMenuOpen(false)
                  navigate('/role')
                }}
                className="flex-1 py-2.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-full hover:bg-gray-50 transition-colors"
              >
                Log In
              </button>
              <button
                onClick={() => {
                  setMobileMenuOpen(false)
                  navigate('/role')
                }}
                className="flex-1 btn-primary py-2.5 text-sm"
              >
                Get Started
              </button>
            </div>
          </div>
        </div>
      )}
    </header>
  )
}
