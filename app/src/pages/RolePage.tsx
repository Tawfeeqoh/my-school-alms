import { useNavigate } from 'react-router-dom'
import Navigation from '../components/Navigation'
import { GraduationCap, UserCog, Building2, ArrowRight, UserCheck } from 'lucide-react'

const roles = [
  {
    icon: GraduationCap,
    title: 'Student',
    description: 'Access courses, submit assignments, track attendance, and manage your academic journey.',
    path: '/register-student',
    color: '#D10000',
    bgColor: 'rgba(209, 0, 0, 0.08)',
  },
  {
    icon: UserCog,
    title: 'Lecturer',
    description: 'Manage courses, grade assignments, upload materials, and monitor student progress.',
    path: '/register-lecturer',
    color: '#2563EB',
    bgColor: 'rgba(37, 99, 235, 0.08)',
  },
  {
    icon: Building2,
    title: 'Institution',
    description: 'Administrative access for managing departments, staff, students, and institutional settings.',
    path: '/register-institution',
    color: '#059669',
    bgColor: 'rgba(5, 150, 105, 0.08)',
  },
]

export default function RolePage() {
  const navigate = useNavigate()

  return (
    <div className="min-h-screen" style={{ backgroundColor: '#F5F5F7' }}>
      <Navigation />
      <div className="pt-24 pb-16 px-4 md:px-8">
        <div className="max-w-4xl mx-auto">
          {/* Header */}
          <div className="text-center mb-12">
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white shadow-sm mb-6">
              <UserCheck className="w-4 h-4 text-[#D10000]" />
              <span className="text-sm font-medium text-gray-700">Select Your Role</span>
            </div>
            <h1 className="text-3xl md:text-4xl font-bold text-black mb-4">
              How will you use <span style={{ color: '#D10000' }}>ALMS?</span>
            </h1>
            <p className="text-gray-600 max-w-lg mx-auto">
              Choose the account type that matches your role at FCAHPT Ibadan. 
              Each role provides tailored tools and features.
            </p>
          </div>

          {/* Role Cards */}
          <div className="grid md:grid-cols-3 gap-6 mb-8">
            {roles.map((role, index) => (
              <button
                key={index}
                onClick={() => navigate(role.path)}
                className="group bg-white rounded-3xl p-8 text-left shadow-sm hover:shadow-lg transition-all duration-300 border-2 border-transparent hover:border-[#D10000]/20"
              >
                <div
                  className="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110"
                  style={{ backgroundColor: role.bgColor }}
                >
                  <role.icon className="w-7 h-7" style={{ color: role.color }} />
                </div>
                <h3 className="text-xl font-bold text-black mb-2">{role.title}</h3>
                <p className="text-sm text-gray-600 leading-relaxed mb-6">
                  {role.description}
                </p>
                <div className="flex items-center gap-2 text-sm font-semibold" style={{ color: role.color }}>
                  Get Started
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </div>
              </button>
            ))}
          </div>

          {/* Sign In Link */}
          <p className="text-center text-sm text-gray-500">
            Already have an account?{' '}
            <button
              onClick={() => navigate('/')}
              className="text-[#D10000] font-semibold hover:underline"
            >
              Sign in here
            </button>
          </p>
        </div>
      </div>
    </div>
  )
}
