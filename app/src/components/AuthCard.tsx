import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { LogIn, User, Users, Building2, Eye, EyeOff, GraduationCap, UserCog } from 'lucide-react'

export default function AuthCard() {
  const navigate = useNavigate()
  const [activeTab, setActiveTab] = useState<'student' | 'staff'>('student')
  const [showPassword, setShowPassword] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [idNumber, setIdNumber] = useState('')
  const [password, setPassword] = useState('')

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    // Simulate login
    setTimeout(() => {
      setIsLoading(false)
      navigate('/dashboard')
    }, 1500)
  }

  return (
    <div className="glass-card-strong w-full max-w-md rounded-3xl p-6 md:p-8">
      {/* Header */}
      <div className="text-center mb-6">
        <div className="flex items-center justify-center gap-3 mb-4">
          <div
            className="w-10 h-10 rounded-xl flex items-center justify-center"
            style={{ backgroundColor: '#D10000' }}
          >
            <GraduationCap className="w-5 h-5 text-white" />
          </div>
          <div>
            <h3 className="text-lg font-bold text-black leading-tight">ALMS</h3>
            <p className="text-[10px] font-mono-data text-gray-500 tracking-widest uppercase">
              FCAHPT IBADAN
            </p>
          </div>
        </div>
        <p className="text-sm text-gray-600">
          Sign in to access your academic workspace
        </p>
      </div>

      {/* Role Tabs */}
      <div className="flex bg-gray-100 rounded-xl p-1 mb-6">
        <button
          onClick={() => setActiveTab('student')}
          className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 ${
            activeTab === 'student'
              ? 'bg-white text-black shadow-sm'
              : 'text-gray-500 hover:text-gray-700'
          }`}
        >
          <User className="w-4 h-4" />
          Student
        </button>
        <button
          onClick={() => setActiveTab('staff')}
          className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 ${
            activeTab === 'staff'
              ? 'bg-white text-black shadow-sm'
              : 'text-gray-500 hover:text-gray-700'
          }`}
        >
          <UserCog className="w-4 h-4" />
          Staff
        </button>
      </div>

      {/* Login Form */}
      <form onSubmit={handleLogin} className="space-y-4">
        <div>
          <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
            {activeTab === 'student' ? 'Matric Number' : 'Staff ID'}
          </label>
          <input
            type="text"
            value={idNumber}
            onChange={(e) => setIdNumber(e.target.value)}
            placeholder={
              activeTab === 'student' ? 'e.g. FCAHPT/2023/001' : 'e.g. STAFF-2023-001'
            }
            className="input-field"
            required
          />
        </div>

        <div>
          <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
            Password
          </label>
          <div className="relative">
            <input
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Enter your password"
              className="input-field pr-12"
              required
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
            </button>
          </div>
        </div>

        <div className="flex items-center justify-between text-sm">
          <label className="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" className="rounded border-gray-300 text-red-600 focus:ring-red-500" />
            <span className="text-gray-600">Remember me</span>
          </label>
          <button type="button" className="text-[#D10000] hover:underline font-medium">
            Forgot password?
          </button>
        </div>

        <button
          type="submit"
          disabled={isLoading}
          className="btn-primary w-full"
          style={{
            opacity: isLoading ? 0.8 : 1,
            transform: isLoading ? 'scale(0.97)' : undefined,
          }}
        >
          {isLoading ? (
            <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
          ) : (
            <>
              <LogIn className="w-4 h-4" />
              Sign In
            </>
          )}
        </button>
      </form>

      {/* Divider */}
      <div className="flex items-center gap-4 my-5">
        <div className="flex-1 h-px bg-gray-200" />
        <span className="text-xs text-gray-400 font-medium uppercase tracking-wider">
          New to ALMS?
        </span>
        <div className="flex-1 h-px bg-gray-200" />
      </div>

      {/* Registration Options */}
      <div className="grid grid-cols-3 gap-2">
        <button
          onClick={() => navigate('/role')}
          className="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-200 hover:border-[#D10000]/30 hover:bg-red-50 transition-all duration-200 group"
        >
          <Users className="w-5 h-5 text-gray-400 group-hover:text-[#D10000] transition-colors" />
          <span className="text-[10px] font-medium text-gray-600 group-hover:text-[#D10000]">
            Student
          </span>
        </button>
        <button
          onClick={() => navigate('/register-lecturer')}
          className="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-200 hover:border-[#D10000]/30 hover:bg-red-50 transition-all duration-200 group"
        >
          <UserCog className="w-5 h-5 text-gray-400 group-hover:text-[#D10000] transition-colors" />
          <span className="text-[10px] font-medium text-gray-600 group-hover:text-[#D10000]">
            Lecturer
          </span>
        </button>
        <button
          onClick={() => navigate('/register-institution')}
          className="flex flex-col items-center gap-1.5 p-3 rounded-xl border border-gray-200 hover:border-[#D10000]/30 hover:bg-red-50 transition-all duration-200 group"
        >
          <Building2 className="w-5 h-5 text-gray-400 group-hover:text-[#D10000] transition-colors" />
          <span className="text-[10px] font-medium text-gray-600 group-hover:text-[#D10000]">
            Institution
          </span>
        </button>
      </div>

      {/* Footer */}
      <p className="text-center text-xs text-gray-500 mt-5">
        By signing in, you agree to our{' '}
        <span className="text-[#D10000] hover:underline cursor-pointer">Terms</span> and{' '}
        <span className="text-[#D10000] hover:underline cursor-pointer">Privacy Policy</span>
      </p>
    </div>
  )
}
