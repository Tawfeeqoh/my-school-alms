import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import Navigation from '../components/Navigation'
import { GraduationCap, Eye, EyeOff, ArrowLeft, CheckCircle2, Loader2 } from 'lucide-react'

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

const levels = ['ND I', 'ND II', 'HND I', 'HND II']

export default function RegisterStudentPage() {
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isSuccess, setIsSuccess] = useState(false)
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    matricNumber: '',
    department: '',
    level: '',
    phone: '',
    password: '',
  })

  const updateField = (field: string, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setIsSubmitting(true)
    setTimeout(() => {
      setIsSubmitting(false)
      setIsSuccess(true)
      setTimeout(() => navigate('/onboarding'), 2000)
    }, 2000)
  }

  if (isSuccess) {
    return (
      <div className="min-h-screen flex items-center justify-center" style={{ backgroundColor: '#F5F5F7' }}>
        <div className="text-center px-4">
          <div className="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6 animate-bounce">
            <CheckCircle2 className="w-10 h-10 text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-black mb-2">Registration Successful!</h2>
          <p className="text-gray-600 mb-6">Welcome to ALMS, {formData.firstName}. Redirecting to onboarding...</p>
          <div className="w-48 h-1 bg-gray-200 rounded-full mx-auto overflow-hidden">
            <div className="h-full bg-[#D10000] rounded-full animate-pulse" />
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen" style={{ backgroundColor: '#F5F5F7' }}>
      <Navigation />
      <div className="pt-24 pb-16 px-4 md:px-8">
        <div className="max-w-xl mx-auto">
          <button
            onClick={() => navigate('/role')}
            className="flex items-center gap-2 text-sm text-gray-500 hover:text-black transition-colors mb-6"
          >
            <ArrowLeft className="w-4 h-4" />
            Back to Roles
          </button>

          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm">
            <div className="flex items-center gap-3 mb-6">
              <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ backgroundColor: '#D10000' }}>
                <GraduationCap className="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 className="text-xl font-bold text-black">Student Registration</h2>
                <p className="text-xs text-gray-500">Create your ALMS student account</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">First Name</label>
                  <input type="text" value={formData.firstName} onChange={(e) => updateField('firstName', e.target.value)} placeholder="John" className="input-field" required />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Last Name</label>
                  <input type="text" value={formData.lastName} onChange={(e) => updateField('lastName', e.target.value)} placeholder="Doe" className="input-field" required />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Email Address</label>
                <input type="email" value={formData.email} onChange={(e) => updateField('email', e.target.value)} placeholder="john.doe@student.fcahpt.edu.ng" className="input-field" required />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Matric Number</label>
                  <input type="text" value={formData.matricNumber} onChange={(e) => updateField('matricNumber', e.target.value)} placeholder="FCAHPT/2023/001" className="input-field" required />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Phone Number</label>
                  <input type="tel" value={formData.phone} onChange={(e) => updateField('phone', e.target.value)} placeholder="+234 800 000 0000" className="input-field" required />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Department</label>
                  <select value={formData.department} onChange={(e) => updateField('department', e.target.value)} className="input-field" required>
                    <option value="">Select</option>
                    {departments.map((dept) => (
                      <option key={dept} value={dept}>{dept}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Level</label>
                  <select value={formData.level} onChange={(e) => updateField('level', e.target.value)} className="input-field" required>
                    <option value="">Select</option>
                    {levels.map((level) => (
                      <option key={level} value={level}>{level}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Password</label>
                <div className="relative">
                  <input type={showPassword ? 'text' : 'password'} value={formData.password} onChange={(e) => updateField('password', e.target.value)} placeholder="Min. 8 characters" className="input-field pr-12" required />
                  <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                  </button>
                </div>
              </div>

              <button type="submit" disabled={isSubmitting} className="btn-primary w-full mt-4">
                {isSubmitting ? (
                  <>
                    <Loader2 className="w-4 h-4 animate-spin" />
                    Creating Account...
                  </>
                ) : (
                  'Register as Student'
                )}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  )
}
