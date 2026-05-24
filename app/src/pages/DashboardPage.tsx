import { useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import gsap from 'gsap'
import DashboardLayout from '../components/DashboardLayout'
import {
  TrendingUp,
  TrendingDown,
  Clock,
  FileText,
  AlertCircle,
  CheckCircle2,
  ChevronRight,
  Sparkles,
  Target,
  BookOpen,
} from 'lucide-react'

const courses = [
  { code: 'AHP 101', name: 'Animal Health & Disease Control', progress: 85, total: 100 },
  { code: 'CS 201', name: 'Data Structures & Algorithms', progress: 62, total: 100 },
  { code: 'SLT 301', name: 'Organic Chemistry II', progress: 45, total: 100 },
  { code: 'STAT 101', name: 'Introduction to Statistics', progress: 78, total: 100 },
  { code: 'BIO 201', name: 'Cell Biology & Genetics', progress: 91, total: 100 },
]

const deadlines = [
  {
    course: 'CS 201',
    title: 'Programming Assignment #3',
    due: '2026-05-23',
    urgent: true,
    submitted: false,
  },
  {
    course: 'AHP 101',
    title: 'Veterinary Practical Report',
    due: '2026-05-25',
    urgent: false,
    submitted: false,
  },
  {
    course: 'SLT 301',
    title: 'Lab Experiment Documentation',
    due: '2026-05-28',
    urgent: false,
    submitted: true,
  },
  {
    course: 'STAT 101',
    title: 'Statistical Analysis Project',
    due: '2026-05-30',
    urgent: false,
    submitted: false,
  },
]

const announcements = [
  {
    title: 'New lecture note uploaded for Comparative Anatomy',
    time: '2 hours ago',
    new: true,
    type: 'material',
  },
  {
    title: 'Exam Timetable released for Second Semester',
    time: '5 hours ago',
    new: true,
    type: 'announcement',
  },
  {
    title: 'Library clearance deadline extended to June 15',
    time: '1 day ago',
    new: false,
    type: 'deadline',
  },
  {
    title: 'AI Study Assistant now available for all courses',
    time: '2 days ago',
    new: false,
    type: 'feature',
  },
]

// Generate attendance data for the current month
const generateAttendanceData = () => {
  const days: { day: number; status: 'present' | 'absent' | 'excused' | 'future' }[] = []
  const today = new Date().getDate()
  for (let i = 1; i <= 31; i++) {
    if (i > today) {
      days.push({ day: i, status: 'future' })
    } else if ([3, 15, 22].includes(i)) {
      days.push({ day: i, status: 'absent' })
    } else if (i === 18) {
      days.push({ day: i, status: 'excused' })
    } else {
      days.push({ day: i, status: 'present' })
    }
  }
  return days
}

const attendanceData = generateAttendanceData()
const presentDays = attendanceData.filter((d) => d.status === 'present').length
const absentDays = attendanceData.filter((d) => d.status === 'absent').length
const excusedDays = attendanceData.filter((d) => d.status === 'excused').length
const overallProgress = 78

export default function DashboardPage() {
  const navigate = useNavigate()
  const progressRef = useRef<SVGCircleElement>(null)
  const widgetsRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    // Animate progress ring
    if (progressRef.current) {
      const circumference = 2 * Math.PI * 80
      const offset = circumference - (overallProgress / 100) * circumference
      gsap.to(progressRef.current, {
        strokeDashoffset: offset,
        duration: 1.5,
        ease: 'power2.out',
        delay: 0.3,
      })
    }

    // Animate widgets
    if (widgetsRef.current) {
      const widgets = widgetsRef.current.querySelectorAll('.dashboard-widget')
      widgets.forEach((widget, index) => {
        gsap.fromTo(
          widget,
          { opacity: 0, y: 24 },
          {
            opacity: 1,
            y: 0,
            duration: 0.5,
            ease: 'power2.out',
            delay: index * 0.1,
          }
        )
      })
    }
  }, [])

  const progressCircumference = 2 * Math.PI * 80

  return (
    <DashboardLayout>
      <div ref={widgetsRef} className="max-w-7xl mx-auto space-y-6">
        {/* Top Stats Row */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {[
            { label: 'CGPA', value: '3.72', change: '+0.15', up: true, icon: Target },
            { label: 'Attendance', value: `${Math.round((presentDays / (presentDays + absentDays)) * 100)}%`, change: 'Good standing', up: true, icon: CheckCircle2 },
            { label: 'Pending', value: '3', change: 'Assignments', up: false, icon: Clock },
            { label: 'Courses', value: '5', change: 'Active now', up: true, icon: BookOpen },
          ].map((stat, index) => (
            <div key={index} className="dashboard-widget bg-white rounded-2xl p-5 shadow-sm">
              <div className="flex items-center justify-between mb-3">
                <stat.icon className="w-5 h-5 text-gray-400" />
                {stat.change && (
                  <span
                    className={`text-xs font-medium flex items-center gap-1 ${
                      stat.up ? 'text-green-600' : 'text-amber-600'
                    }`}
                  >
                    {stat.up ? (
                      <TrendingUp className="w-3 h-3" />
                    ) : (
                      <TrendingDown className="w-3 h-3" />
                    )}
                    {stat.change}
                  </span>
                )}
              </div>
              <div className="text-2xl font-bold text-black">{stat.value}</div>
              <div className="text-xs text-gray-500 mt-1">{stat.label}</div>
            </div>
          ))}
        </div>

        {/* Main Content Grid */}
        <div className="grid lg:grid-cols-3 gap-6">
          {/* Left Column - 2/3 */}
          <div className="lg:col-span-2 space-y-6">
            {/* Academic Progress */}
            <div className="dashboard-widget bg-white rounded-3xl p-6 md:p-8 shadow-sm">
              <div className="flex items-center justify-between mb-6">
                <div>
                  <h3 className="text-lg font-semibold text-black">Academic Progress</h3>
                  <p className="text-sm text-gray-500 mt-0.5">
                    Overall completion across all courses
                  </p>
                </div>
                <button
                  onClick={() => navigate('/analytics')}
                  className="text-sm text-[#D10000] font-medium flex items-center gap-1 hover:underline"
                >
                  View Details
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>

              <div className="flex flex-col sm:flex-row items-center gap-8">
                {/* Radial Progress */}
                <div className="relative flex-shrink-0">
                  <svg width="180" height="180" viewBox="0 0 180 180">
                    <circle
                      cx="90"
                      cy="90"
                      r="80"
                      fill="none"
                      stroke="#F5F5F7"
                      strokeWidth="12"
                    />
                    <circle
                      ref={progressRef}
                      cx="90"
                      cy="90"
                      r="80"
                      fill="none"
                      stroke="#D10000"
                      strokeWidth="12"
                      strokeLinecap="round"
                      strokeDasharray={progressCircumference}
                      strokeDashoffset={progressCircumference}
                      className="progress-ring-circle"
                    />
                  </svg>
                  <div className="absolute inset-0 flex flex-col items-center justify-center">
                    <span className="text-3xl font-bold text-black">{overallProgress}%</span>
                    <span className="text-xs text-gray-500">Complete</span>
                  </div>
                </div>

                {/* Course Progress Bars */}
                <div className="flex-1 w-full space-y-4">
                  {courses.map((course, index) => (
                    <div key={index}>
                      <div className="flex items-center justify-between mb-1.5">
                        <div className="flex items-center gap-2">
                          <span className="text-xs font-mono-data font-semibold text-[#D10000]">
                            {course.code}
                          </span>
                          <span className="text-sm text-gray-700 truncate max-w-[200px]">
                            {course.name}
                          </span>
                        </div>
                        <span className="text-xs font-mono-data text-gray-500">
                          {course.progress}%
                        </span>
                      </div>
                      <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div
                          className="h-full rounded-full transition-all duration-700"
                          style={{
                            width: `${course.progress}%`,
                            backgroundColor: '#D10000',
                          }}
                        />
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Upcoming Deadlines */}
            <div className="dashboard-widget bg-white rounded-3xl p-6 md:p-8 shadow-sm">
              <div className="flex items-center justify-between mb-6">
                <div>
                  <h3 className="text-lg font-semibold text-black">Upcoming Deadlines</h3>
                  <p className="text-sm text-gray-500 mt-0.5">
                    Stay on top of your submissions
                  </p>
                </div>
                <button className="text-sm text-[#D10000] font-medium hover:underline">
                  View All
                </button>
              </div>

              <div className="space-y-3">
                {deadlines.map((deadline, index) => (
                  <div
                    key={index}
                    className="flex items-center gap-4 p-4 rounded-2xl border border-gray-100 hover:border-gray-200 transition-all group"
                  >
                    <div
                      className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${
                        deadline.submitted
                          ? 'bg-green-50'
                          : deadline.urgent
                          ? 'bg-red-50'
                          : 'bg-amber-50'
                      }`}
                    >
                      {deadline.submitted ? (
                        <CheckCircle2 className="w-5 h-5 text-green-600" />
                      ) : deadline.urgent ? (
                        <AlertCircle className="w-5 h-5 text-[#D10000]" />
                      ) : (
                        <Clock className="w-5 h-5 text-amber-600" />
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <span className="text-xs font-mono-data font-semibold text-[#D10000]">
                          {deadline.course}
                        </span>
                        {deadline.urgent && !deadline.submitted && (
                          <span className="text-[10px] font-semibold bg-[#D10000] text-white px-2 py-0.5 rounded-full">
                            DUE SOON
                          </span>
                        )}
                        {deadline.submitted && (
                          <span className="text-[10px] font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                            SUBMITTED
                          </span>
                        )}
                      </div>
                      <p className="text-sm font-medium text-black truncate">
                        {deadline.title}
                      </p>
                      <p className="text-xs text-gray-500">
                        Due {new Date(deadline.due).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                      </p>
                    </div>
                    {!deadline.submitted && (
                      <button className="btn-outline text-xs py-2 px-4 opacity-0 group-hover:opacity-100 transition-opacity">
                        Submit
                      </button>
                    )}
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Right Column - 1/3 */}
          <div className="space-y-6">
            {/* Attendance Pulse */}
            <div className="dashboard-widget bg-white rounded-3xl p-6 shadow-sm">
              <h3 className="text-lg font-semibold text-black mb-1">Attendance Pulse</h3>
              <p className="text-sm text-gray-500 mb-4">{new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</p>

              {/* Dot Grid Calendar */}
              <div className="grid grid-cols-7 gap-2 mb-4">
                {['S', 'M', 'T', 'W', 'T', 'F', 'S'].map((day, i) => (
                  <div key={i} className="text-center text-[10px] font-mono-data text-gray-400 mb-1">
                    {day}
                  </div>
                ))}
                {attendanceData.map((day, i) => {
                  const isToday = day.day === new Date().getDate()
                  return (
                    <div
                      key={i}
                      className={`aspect-square rounded-full flex items-center justify-center text-[10px] font-mono-data ${
                        day.status === 'future'
                          ? 'bg-gray-50 text-gray-300'
                          : day.status === 'present'
                          ? 'bg-black text-white'
                          : day.status === 'excused'
                          ? 'bg-amber-100 text-amber-700 border border-amber-300'
                          : 'bg-transparent border border-gray-300 text-gray-500'
                      } ${isToday ? 'ring-2 ring-[#D10000] ring-offset-1' : ''}`}
                    >
                      {day.day}
                    </div>
                  )
                })}
              </div>

              {/* Summary */}
              <div className="flex items-center justify-between text-xs pt-3 border-t border-gray-100">
                <div className="flex items-center gap-4">
                  <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-black" />
                    <span className="text-gray-600">{presentDays} Present</span>
                  </span>
                  <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-amber-400" />
                    <span className="text-gray-600">{excusedDays} Excused</span>
                  </span>
                  <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full border border-gray-400" />
                    <span className="text-gray-600">{absentDays} Absent</span>
                  </span>
                </div>
              </div>
            </div>

            {/* AI Assistant Card */}
            <div
              className="dashboard-widget rounded-3xl p-6 shadow-sm cursor-pointer group"
              style={{ backgroundColor: '#D10000' }}
              onClick={() => navigate('/ai-assistant')}
            >
              <div className="flex items-start justify-between mb-4">
                <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                  <Sparkles className="w-5 h-5 text-white" />
                </div>
                <ChevronRight className="w-5 h-5 text-white/60 group-hover:text-white transition-colors" />
              </div>
              <h3 className="text-lg font-semibold text-white mb-1">AI Study Assistant</h3>
              <p className="text-sm text-white/80 mb-4">
                Get help with assignments, explanations, and study planning.
              </p>
              <button className="w-full py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium text-white transition-colors">
                Start Chatting
              </button>
            </div>

            {/* Announcements */}
            <div className="dashboard-widget bg-white rounded-3xl p-6 shadow-sm">
              <h3 className="text-lg font-semibold text-black mb-4">Announcements</h3>
              <div className="space-y-3">
                {announcements.map((item, index) => (
                  <div
                    key={index}
                    className="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
                  >
                    <div className="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                      <FileText className="w-4 h-4 text-gray-500" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm text-black leading-snug">{item.title}</p>
                      <div className="flex items-center gap-2 mt-1">
                        <span className="text-[10px] text-gray-400">{item.time}</span>
                        {item.new && (
                          <span className="text-[9px] font-bold bg-[#D10000] text-white px-1.5 py-0.5 rounded">
                            NEW
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  )
}
