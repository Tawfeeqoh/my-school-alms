import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import Navigation from '../components/Navigation'
import { Eye, EyeOff, GraduationCap, ArrowLeft, CheckCircle2 } from 'lucide-react'

export default function SignupPage() {
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [step, setStep] = useState(1)
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    matricNumber: '',
    department: '',
    password: '',
    confirmPassword: '',
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (step < 3) {
      setStep(step + 1)
    } else {
      setIsLoading(true)
      setTimeout(() => {
        setIsLoading(false)
        navigate('/onboarding')
      }, 1500)
    }
  }

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

  const updateField = (field: string, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }))
  }

  return (
    <div className="min-h-screen" style={{ backgroundColor: '#F5F5F7' }}>
      <Navigation />
      <div className="pt-24 pb-16 px-4 md:px-8">
        <div className="max-w-lg mx-auto">
          {/* Back Button */}
          <button
            onClick={() => {
              if (step > 1) setStep(step - 1)
              else navigate('/role')
            }}
            className="flex items-center gap-2 text-sm text-gray-500 hover:text-black transition-colors mb-6"
          >
            <ArrowLeft className="w-4 h-4" />
            {step > 1 ? 'Back' : 'Select Different Role'}
          </button>

          {/* Progress Steps */}
          <div className="flex items-center gap-3 mb-8">
            {[1, 2, 3].map((s) => (
              <div key={s} className="flex items-center gap-3 flex-1">
                <div
                  className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all ${
                    s < step
                      ? 'bg-black text-white'
                      : s === step
                      ? 'bg-[#D10000] text-white'
                      : 'bg-gray-200 text-gray-500'
                  }`}
                >
                  {s < step ? <CheckCircle2 className="w-4 h-4" /> : s}
                </div>
                {s < 3 && (
                  <div
                    className={`flex-1 h-[2px] rounded-full ${
                      s < step ? 'bg-black' : 'bg-gray-200'
                    }`}
                  />
                )}
              </div>
            ))}
          </div>

          {/* Card */}
          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm">
            <div className="flex items-center gap-3 mb-6">
              <div
                className="w-10 h-10 rounded-xl flex items-center justify-center"
                style={{ backgroundColor: '#D10000' }}
              >
                <GraduationCap className="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 className="text-xl font-bold text-black">
                  {step === 1 && 'Personal Information'}
                  {step === 2 && 'Academic Details'}
                  {step === 3 && 'Security'}
                </h2>
                <p className="text-xs text-gray-500">
                  Step {step} of 3
                </p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              {step === 1 && (
                <>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                        First Name
                      </label>
                      <input
                        type="text"
                        value={formData.firstName}
                        onChange={(e) => updateField('firstName', e.target.value)}
                        placeholder="John"
                        className="input-field"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                        Last Name
                      </label>
                      <input
                        type="text"
                        value={formData.lastName}
                        onChange={(e) => updateField('lastName', e.target.value)}
                        placeholder="Doe"
                        className="input-field"
                        required
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                      Email Address
                    </label>
                    <input
                      type="email"
                      value={formData.email}
                      onChange={(e) => updateField('email', e.target.value)}
                      placeholder="john.doe@student.fcahpt.edu.ng"
                      className="input-field"
                      required
                    />
                  </div>
                </>
              )}

              {step === 2 && (
                <>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                      Matric Number
                    </label>
                    <input
                      type="text"
                      value={formData.matricNumber}
                      onChange={(e) => updateField('matricNumber', e.target.value)}
                      placeholder="FCAHPT/2023/001"
                      className="input-field"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                      Department
                    </label>
                    <select
                      value={formData.department}
                      onChange={(e) => updateField('department', e.target.value)}
                      className="input-field"
                      required
                    >
                      <option value="">Select your department</option>
                      {departments.map((dept) => (
                        <option key={dept} value={dept}>
                          {dept}
                        </option>
                      ))}
                    </select>
                  </div>
                </>
              )}

              {step === 3 && (
                <>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                      Password
                    </label>
                    <div className="relative">
                      <input
                        type={showPassword ? 'text' : 'password'}
                        value={formData.password}
                        onChange={(e) => updateField('password', e.target.value)}
                        placeholder="Min. 8 characters"
                        className="input-field pr-12"
                        required
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                      </button>
                    </div>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                      Confirm Password
                    </label>
                    <input
                      type="password"
                      value={formData.confirmPassword}
                      onChange={(e) => updateField('confirmPassword', e.target.value)}
                      placeholder="Re-enter your password"
                      className="input-field"
                      required
                    />
                  </div>
                </>
              )}

              <button
                type="submit"
                disabled={isLoading}
                className="btn-primary w-full mt-6"
              >
                {isLoading ? (
                  <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  step === 3 ? 'Create Account' : 'Continue'
                )}
              </button>
            </form>
          </div>

          {/* Footer */}
          <p className="text-center text-sm text-gray-500 mt-6">
            Already have an account?{' '}
            <button
              onClick={() => navigate('/')}
              className="text-[#D10000] font-semibold hover:underline"
            >
              Sign in
            </button>
          </p>
        </div>
      </div>
    </div>
  )
}
