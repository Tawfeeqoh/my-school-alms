import { useState, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import gsap from 'gsap'
import {
  Sparkles,
  BookOpen,
  BarChart3,
  Brain,
  Bell,
  CheckCircle2,
  ChevronRight,
  GraduationCap,
} from 'lucide-react'

const steps = [
  {
    icon: Sparkles,
    title: 'Welcome to ALMS',
    description: 'Your academic journey just got a whole lot easier. ALMS is designed around how you actually learn.',
    color: '#D10000',
  },
  {
    icon: BookOpen,
    title: 'All Your Courses in One Place',
    description: 'Access lecture notes, assignments, and course materials organized by your department and level.',
    color: '#2563EB',
  },
  {
    icon: BarChart3,
    title: 'Track Your Progress',
    description: 'Monitor your attendance, grades, and academic performance with real-time analytics.',
    color: '#059669',
  },
  {
    icon: Brain,
    title: 'AI Study Assistant',
    description: 'Get personalized help with your studies. Ask questions, get explanations, and plan your learning.',
    color: '#7C3AED',
  },
  {
    icon: Bell,
    title: 'Never Miss a Deadline',
    description: 'Receive timely notifications for assignments, exams, and important announcements.',
    color: '#D97706',
  },
]

export default function OnboardingPage() {
  const navigate = useNavigate()
  const [currentStep, setCurrentStep] = useState(0)
  const [isAnimating, setIsAnimating] = useState(false)
  const contentRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    if (contentRef.current) {
      gsap.fromTo(
        contentRef.current,
        { opacity: 0, y: 30, filter: 'blur(8px)' },
        { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.6, ease: 'power2.out' }
      )
    }
  }, [currentStep])

  const handleNext = () => {
    if (isAnimating) return
    setIsAnimating(true)
    if (currentStep < steps.length - 1) {
      setCurrentStep((prev) => prev + 1)
      setTimeout(() => setIsAnimating(false), 600)
    } else {
      navigate('/dashboard')
    }
  }

  const handleSkip = () => {
    navigate('/dashboard')
  }

  const step = steps[currentStep]

  return (
    <div className="min-h-screen flex flex-col items-center justify-center px-4" style={{ backgroundColor: '#F5F5F7' }}>
      {/* Logo */}
      <div className="flex items-center gap-3 mb-12">
        <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ backgroundColor: '#D10000' }}>
          <GraduationCap className="w-5 h-5 text-white" />
        </div>
        <div>
          <span className="text-lg font-bold text-black">ALMS</span>
          <span className="block text-[9px] font-mono-data text-gray-500 tracking-widest uppercase">FCAHPT IBADAN</span>
        </div>
      </div>

      {/* Content */}
      <div ref={contentRef} className="max-w-md w-full text-center">
        {/* Icon */}
        <div
          className="w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-8"
          style={{ backgroundColor: `${step.color}15` }}
        >
          <step.icon className="w-10 h-10" style={{ color: step.color }} />
        </div>

        {/* Text */}
        <h2 className="text-2xl md:text-3xl font-bold text-black mb-4">{step.title}</h2>
        <p className="text-gray-600 leading-relaxed mb-8">{step.description}</p>

        {/* Progress Dots */}
        <div className="flex items-center justify-center gap-2 mb-8">
          {steps.map((_, index) => (
            <div
              key={index}
              className={`h-2 rounded-full transition-all duration-300 ${
                index === currentStep
                  ? 'w-8'
                  : index < currentStep
                  ? 'w-2'
                  : 'w-2 bg-gray-300'
              }`}
              style={{
                backgroundColor:
                  index === currentStep
                    ? step.color
                    : index < currentStep
                    ? step.color
                    : undefined,
              }}
            />
          ))}
        </div>

        {/* Actions */}
        <div className="space-y-3">
          <button
            onClick={handleNext}
            className="btn-primary w-full justify-center"
            style={{ backgroundColor: step.color }}
          >
            {currentStep === steps.length - 1 ? (
              <>
                <CheckCircle2 className="w-4 h-4" />
                Get Started
              </>
            ) : (
              <>
                Continue
                <ChevronRight className="w-4 h-4" />
              </>
            )}
          </button>
          {currentStep < steps.length - 1 && (
            <button
              onClick={handleSkip}
              className="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors"
            >
              Skip Tour
            </button>
          )}
        </div>
      </div>
    </div>
  )
}
