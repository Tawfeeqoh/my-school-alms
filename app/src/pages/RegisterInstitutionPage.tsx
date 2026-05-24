import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import Navigation from '../components/Navigation'
import { Building2, Eye, EyeOff, ArrowLeft, CheckCircle2, Loader2 } from 'lucide-react'

export default function RegisterInstitutionPage() {
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isSuccess, setIsSuccess] = useState(false)
  const [formData, setFormData] = useState({
    institutionName: '',
    adminName: '',
    adminEmail: '',
    adminPhone: '',
    role: '',
    staffId: '',
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
      setTimeout(() => navigate('/dashboard'), 2000)
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
          <p className="text-gray-600 mb-6">Institutional access granted. Redirecting to dashboard...</p>
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
          <button onClick={() => navigate('/role')} className="flex items-center gap-2 text-sm text-gray-500 hover:text-black transition-colors mb-6">
            <ArrowLeft className="w-4 h-4" />
            Back to Roles
          </button>

          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm">
            <div className="flex items-center gap-3 mb-6">
              <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ backgroundColor: '#059669' }}>
                <Building2 className="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 className="text-xl font-bold text-black">Institution Registration</h2>
                <p className="text-xs text-gray-500">Request administrative access for FCAHPT</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Institution Name</label>
                <input type="text" value={formData.institutionName} onChange={(e) => updateField('institutionName', e.target.value)} placeholder="Federal College of Animal Health & Production Technology" className="input-field" required />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Administrator Name</label>
                  <input type="text" value={formData.adminName} onChange={(e) => updateField('adminName', e.target.value)} placeholder="Full name" className="input-field" required />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Admin Role</label>
                  <select value={formData.role} onChange={(e) => updateField('role', e.target.value)} className="input-field" required>
                    <option value="">Select</option>
                    <option value="registrar">College Registrar</option>
                    <option value="hods">Head of Department</option>
                    <option value="it">IT Administrator</option>
                    <option value="provost">Provost</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Admin Email</label>
                <input type="email" value={formData.adminEmail} onChange={(e) => updateField('adminEmail', e.target.value)} placeholder="admin@fcahpt.edu.ng" className="input-field" required />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Phone</label>
                  <input type="tel" value={formData.adminPhone} onChange={(e) => updateField('adminPhone', e.target.value)} placeholder="+234 800 000 0000" className="input-field" required />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Staff ID</label>
                  <input type="text" value={formData.staffId} onChange={(e) => updateField('staffId', e.target.value)} placeholder="STAFF-2023-001" className="input-field" required />
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
                    Processing...
                  </>
                ) : (
                  'Request Access'
                )}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  )
}
