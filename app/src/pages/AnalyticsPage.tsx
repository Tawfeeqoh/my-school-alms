import { useEffect, useRef } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import { TrendingUp, Target, Award, BookOpen, CalendarCheck } from 'lucide-react'

const courseGrades = [
  { code: 'AHP 101', name: 'Animal Health', ca: 28, exam: 58, total: 86, grade: 'A' },
  { code: 'CS 201', name: 'Data Structures', ca: 22, exam: 45, total: 67, grade: 'B' },
  { code: 'SLT 301', name: 'Organic Chemistry', ca: 18, exam: 35, total: 53, grade: 'C' },
  { code: 'STAT 101', name: 'Statistics', ca: 25, exam: 55, total: 80, grade: 'A' },
  { code: 'BIO 201', name: 'Cell Biology', ca: 30, exam: 62, total: 92, grade: 'A' },
]

const semesterData = [
  { semester: '1st Sem 2023', gpa: 3.45 },
  { semester: '2nd Sem 2023', gpa: 3.62 },
  { semester: '1st Sem 2024', gpa: 3.58 },
  { semester: '2nd Sem 2024', gpa: 3.71 },
  { semester: '1st Sem 2025', gpa: 3.68 },
  { semester: '2nd Sem 2025', gpa: 3.72 },
]

const attendanceByCourse = [
  { code: 'AHP 101', attended: 18, total: 20, rate: 90 },
  { code: 'CS 201', attended: 15, total: 20, rate: 75 },
  { code: 'SLT 301', attended: 12, total: 20, rate: 60 },
  { code: 'STAT 101', attended: 19, total: 20, rate: 95 },
  { code: 'BIO 201', attended: 20, total: 20, rate: 100 },
]

export default function AnalyticsPage() {
  const barsRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    if (barsRef.current) {
      const bars = barsRef.current.querySelectorAll('.semester-bar')
      bars.forEach((bar, index) => {
        const el = bar as HTMLElement
        const height = semesterData[index].gpa / 4 * 100
        setTimeout(() => {
          el.style.height = `${height}%`
        }, index * 150)
      })
    }
  }, [])

  const getGradeColor = (grade: string) => {
    switch (grade) {
      case 'A': return 'text-green-600 bg-green-50'
      case 'B': return 'text-blue-600 bg-blue-50'
      case 'C': return 'text-amber-600 bg-amber-50'
      default: return 'text-gray-600 bg-gray-50'
    }
  }

  const cgpa = 3.72

  return (
    <DashboardLayout>
      <div className="max-w-7xl mx-auto space-y-6">
        {/* Header */}
        <div>
          <h2 className="text-2xl font-bold text-black">Academic Analytics</h2>
          <p className="text-sm text-gray-500 mt-0.5">Track your performance and progress</p>
        </div>

        {/* Stats Row */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {[
            { label: 'Current CGPA', value: cgpa.toFixed(2), icon: Target, color: '#D10000', sub: 'Out of 4.00' },
            { label: 'Total Credits', value: '48', icon: BookOpen, color: '#2563EB', sub: 'This semester' },
            { label: 'Attendance', value: '84%', icon: CalendarCheck, color: '#059669', sub: 'Average across courses' },
            { label: 'Standing', value: 'Upper Credit', icon: Award, color: '#7C3AED', sub: 'Excellent performance' },
          ].map((stat, index) => (
            <div key={index} className="bg-white rounded-2xl p-5 shadow-sm">
              <div className="flex items-center justify-between mb-3">
                <stat.icon className="w-5 h-5" style={{ color: stat.color }} />
              </div>
              <div className="text-2xl font-bold text-black">{stat.value}</div>
              <div className="text-xs text-gray-500 mt-0.5">{stat.label}</div>
              <div className="text-[10px] text-gray-400 mt-1">{stat.sub}</div>
            </div>
          ))}
        </div>

        <div className="grid lg:grid-cols-2 gap-6">
          {/* GPA Trend */}
          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm">
            <div className="flex items-center justify-between mb-6">
              <div>
                <h3 className="text-lg font-semibold text-black">CGPA Trend</h3>
                <p className="text-sm text-gray-500">Performance over semesters</p>
              </div>
              <div className="flex items-center gap-1 text-green-600 text-sm font-medium">
                <TrendingUp className="w-4 h-4" />
                +0.27
              </div>
            </div>

            {/* Bar Chart */}
            <div ref={barsRef} className="flex items-end justify-between gap-3 h-48 px-2">
              {semesterData.map((data, index) => (
                <div key={index} className="flex-1 flex flex-col items-center gap-2">
                  <span className="text-xs font-mono-data font-semibold text-gray-600">{data.gpa.toFixed(2)}</span>
                  <div className="w-full flex justify-center">
                    <div
                      className="w-full max-w-[40px] rounded-t-lg transition-all duration-700 ease-out"
                      style={{
                        backgroundColor: '#D10000',
                        height: '0%',
                        opacity: 0.7 + (index / semesterData.length) * 0.3,
                      }}
                    />
                  </div>
                  <span className="text-[10px] text-gray-400 text-center leading-tight">{data.semester}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Grade Distribution */}
          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm">
            <h3 className="text-lg font-semibold text-black mb-1">Grade Distribution</h3>
            <p className="text-sm text-gray-500 mb-6">Current semester performance</p>

            <div className="space-y-4">
              {courseGrades.map((course, index) => (
                <div key={index} className="flex items-center gap-4">
                  <div className="flex-1">
                    <div className="flex items-center justify-between mb-1">
                      <div className="flex items-center gap-2">
                        <span className="text-xs font-mono-data font-bold text-[#D10000]">{course.code}</span>
                        <span className="text-sm text-gray-700">{course.name}</span>
                      </div>
                      <span className={`text-xs font-bold px-2 py-0.5 rounded-full ${getGradeColor(course.grade)}`}>
                        {course.grade}
                      </span>
                    </div>
                    <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                      <div
                        className="h-full rounded-full"
                        style={{
                          width: `${course.total}%`,
                          backgroundColor: course.total >= 70 ? '#059669' : course.total >= 60 ? '#D97706' : '#D10000',
                        }}
                      />
                    </div>
                    <div className="flex items-center gap-3 mt-1">
                      <span className="text-[10px] text-gray-400">CA: {course.ca}/40</span>
                      <span className="text-[10px] text-gray-400">Exam: {course.exam}/60</span>
                      <span className="text-[10px] font-mono-data font-semibold text-gray-600">Total: {course.total}%</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Attendance Breakdown */}
        <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm">
          <h3 className="text-lg font-semibold text-black mb-1">Attendance by Course</h3>
          <p className="text-sm text-gray-500 mb-6">Monitor your class attendance</p>

          <div className="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
            {attendanceByCourse.map((course, index) => (
              <div key={index} className="text-center p-4 rounded-2xl bg-gray-50">
                <div className="relative w-20 h-20 mx-auto mb-3">
                  <svg viewBox="0 0 80 80" className="w-full h-full -rotate-90">
                    <circle cx="40" cy="40" r="32" fill="none" stroke="#E5E7EB" strokeWidth="8" />
                    <circle
                      cx="40" cy="40" r="32" fill="none"
                      stroke={course.rate >= 80 ? '#059669' : course.rate >= 60 ? '#D97706' : '#D10000'}
                      strokeWidth="8"
                      strokeLinecap="round"
                      strokeDasharray={`${course.rate * 2.01} ${200 - course.rate * 2.01}`}
                    />
                  </svg>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-sm font-bold text-black">{course.rate}%</span>
                  </div>
                </div>
                <span className="text-xs font-mono-data font-bold text-[#D10000]">{course.code}</span>
                <p className="text-xs text-gray-500 mt-0.5">{course.attended}/{course.total} classes</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </DashboardLayout>
  )
}
