import { useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import {
  Camera,
  Mail,
  Phone,
  MapPin,
  BookOpen,
  Calendar,
  Award,
  Edit3,
  Save,
  GraduationCap,
  Shield,
} from 'lucide-react'

export default function ProfilePage() {
  const [isEditing, setIsEditing] = useState(false)
  const [profile, setProfile] = useState({
    firstName: 'Amara',
    lastName: 'Okafor',
    email: 'amara.okafor@student.fcahpt.edu.ng',
    phone: '+234 803 456 7890',
    matricNumber: 'FCAHPT/2022/0847',
    department: 'Computer Science',
    level: 'ND II',
    program: 'National Diploma',
    entryYear: '2022',
    expectedGraduation: '2026',
    bio: 'Computer Science student passionate about software development and data science. Currently exploring AI/ML applications in agriculture.',
  })

  const updateField = (field: string, value: string) => {
    setProfile((prev) => ({ ...prev, [field]: value }))
  }

  return (
    <DashboardLayout>
      <div className="max-w-4xl mx-auto">
        {/* Profile Header */}
        <div className="bg-white rounded-3xl overflow-hidden shadow-sm mb-6">
          {/* Cover */}
          <div className="h-32 md:h-48 relative" style={{ backgroundColor: '#D10000' }}>
            <div className="absolute inset-0 opacity-20" style={{
              backgroundImage: 'url(/images/campus.jpg)',
              backgroundSize: 'cover',
              backgroundPosition: 'center',
            }} />
            <button
              onClick={() => setIsEditing(!isEditing)}
              className="absolute top-4 right-4 flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-medium hover:bg-white/30 transition-colors"
            >
              {isEditing ? <Save className="w-4 h-4" /> : <Edit3 className="w-4 h-4" />}
              {isEditing ? 'Save Profile' : 'Edit Profile'}
            </button>
          </div>

          {/* Avatar & Info */}
          <div className="px-6 md:px-8 pb-6">
            <div className="relative -mt-16 mb-4">
              <div className="w-32 h-32 rounded-3xl overflow-hidden ring-4 ring-white shadow-lg relative">
                <img
                  src="/images/avatar-student.jpg"
                  alt="Profile"
                  className="w-full h-full object-cover"
                />
                {isEditing && (
                  <button className="absolute inset-0 bg-black/40 flex items-center justify-center hover:bg-black/50 transition-colors">
                    <Camera className="w-6 h-6 text-white" />
                  </button>
                )}
              </div>
            </div>

            <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
              <div>
                <h2 className="text-2xl font-bold text-black">
                  {profile.firstName} {profile.lastName}
                </h2>
                <p className="text-sm text-gray-500 mt-0.5 flex items-center gap-2">
                  <span className="font-mono-data text-[#D10000] font-semibold">{profile.matricNumber}</span>
                  <span className="text-gray-300">|</span>
                  <span>{profile.department}</span>
                </p>
              </div>
              <div className="flex items-center gap-2">
                <span className="px-3 py-1.5 bg-[#D10000]/10 text-[#D10000] text-xs font-semibold rounded-full">
                  {profile.level}
                </span>
                <span className="px-3 py-1.5 bg-green-50 text-green-700 text-xs font-semibold rounded-full">
                  Active
                </span>
              </div>
            </div>

            {isEditing ? (
              <textarea
                value={profile.bio}
                onChange={(e) => updateField('bio', e.target.value)}
                className="input-field mt-4 resize-none"
                rows={3}
              />
            ) : (
              <p className="text-sm text-gray-600 mt-4 leading-relaxed">{profile.bio}</p>
            )}
          </div>
        </div>

        {/* Info Grid */}
        <div className="grid md:grid-cols-2 gap-6">
          {/* Personal Information */}
          <div className="bg-white rounded-3xl p-6 shadow-sm">
            <h3 className="text-lg font-semibold text-black mb-4 flex items-center gap-2">
              <Shield className="w-5 h-5 text-gray-400" />
              Personal Information
            </h3>
            <div className="space-y-4">
              {[
                { label: 'Email', value: profile.email, icon: Mail, field: 'email' },
                { label: 'Phone', value: profile.phone, icon: Phone, field: 'phone' },
                { label: 'Department', value: profile.department, icon: BookOpen, field: 'department' },
                { label: 'Program', value: profile.program, icon: GraduationCap, field: 'program' },
              ].map((item, index) => (
                <div key={index} className="flex items-start gap-3">
                  <div className="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <item.icon className="w-4 h-4 text-gray-500" />
                  </div>
                  <div className="flex-1">
                    <label className="text-xs text-gray-500 uppercase tracking-wider">{item.label}</label>
                    {isEditing ? (
                      <input
                        type="text"
                        value={item.value}
                        onChange={(e) => updateField(item.field, e.target.value)}
                        className="input-field mt-1"
                      />
                    ) : (
                      <p className="text-sm font-medium text-black mt-0.5">{item.value}</p>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Academic Details */}
          <div className="bg-white rounded-3xl p-6 shadow-sm">
            <h3 className="text-lg font-semibold text-black mb-4 flex items-center gap-2">
              <Award className="w-5 h-5 text-gray-400" />
              Academic Details
            </h3>
            <div className="space-y-4">
              {[
                { label: 'Matric Number', value: profile.matricNumber, icon: Shield },
                { label: 'Level', value: profile.level, icon: BookOpen },
                { label: 'Entry Year', value: profile.entryYear, icon: Calendar },
                { label: 'Expected Graduation', value: profile.expectedGraduation, icon: MapPin },
              ].map((item, index) => (
                <div key={index} className="flex items-start gap-3">
                  <div className="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <item.icon className="w-4 h-4 text-gray-500" />
                  </div>
                  <div className="flex-1">
                    <label className="text-xs text-gray-500 uppercase tracking-wider">{item.label}</label>
                    <p className="text-sm font-medium text-black mt-0.5">{item.value}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Academic Summary */}
        <div className="bg-white rounded-3xl p-6 shadow-sm mt-6">
          <h3 className="text-lg font-semibold text-black mb-4">Academic Summary</h3>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {[
              { label: 'CGPA', value: '3.72', color: '#D10000' },
              { label: 'Courses', value: '5', color: '#2563EB' },
              { label: 'Credits', value: '48', color: '#059669' },
              { label: 'Standing', value: 'Upper Credit', color: '#7C3AED' },
            ].map((stat, index) => (
              <div key={index} className="text-center p-4 rounded-2xl" style={{ backgroundColor: `${stat.color}08` }}>
                <div className="text-2xl font-bold" style={{ color: stat.color }}>{stat.value}</div>
                <div className="text-xs text-gray-500 mt-1">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </DashboardLayout>
  )
}
