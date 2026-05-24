import { useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import { Search, BookOpen, Clock, Users, Star, Filter, ChevronRight } from 'lucide-react'

const allCourses = [
  { code: 'CS 201', name: 'Data Structures & Algorithms', lecturer: 'Dr. Adeyemi', credits: 4, students: 45, rating: 4.8, image: '/images/course-cs.jpg', progress: 62, category: 'Computer Science' },
  { code: 'CS 301', name: 'Software Engineering', lecturer: 'Prof. Okafor', credits: 3, students: 38, rating: 4.6, image: '/images/course-cs.jpg', progress: 30, category: 'Computer Science' },
  { code: 'AHP 101', name: 'Animal Health & Disease Control', lecturer: 'Dr. Ibrahim', credits: 4, students: 52, rating: 4.9, image: '/images/course-animal-health.jpg', progress: 85, category: 'Animal Health' },
  { code: 'AHP 201', name: 'Veterinary Pharmacology', lecturer: 'Dr. Nwosu', credits: 3, students: 41, rating: 4.7, image: '/images/course-animal-health.jpg', progress: 45, category: 'Animal Health' },
  { code: 'SLT 301', name: 'Organic Chemistry II', lecturer: 'Prof. Abdullahi', credits: 4, students: 48, rating: 4.5, image: '/images/course-slt.jpg', progress: 45, category: 'Science Lab Tech' },
  { code: 'SLT 201', name: 'Biochemistry', lecturer: 'Dr. Emeka', credits: 3, students: 44, rating: 4.4, image: '/images/course-slt.jpg', progress: 70, category: 'Science Lab Tech' },
  { code: 'STAT 101', name: 'Introduction to Statistics', lecturer: 'Dr. Hassan', credits: 3, students: 65, rating: 4.3, image: '/images/course-cs.jpg', progress: 78, category: 'Statistics' },
  { code: 'BIO 201', name: 'Cell Biology & Genetics', lecturer: 'Prof. Chukwu', credits: 4, students: 50, rating: 4.7, image: '/images/course-animal-health.jpg', progress: 91, category: 'Biology' },
  { code: 'PHY 101', name: 'General Physics I', lecturer: 'Dr. Musa', credits: 3, students: 55, rating: 4.2, image: '/images/course-slt.jpg', progress: 55, category: 'Physics' },
  { code: 'MICRO 201', name: 'Medical Microbiology', lecturer: 'Dr. Okonkwo', credits: 4, students: 42, rating: 4.6, image: '/images/course-animal-health.jpg', progress: 38, category: 'Microbiology' },
]

const categories = ['All', 'Computer Science', 'Animal Health', 'Science Lab Tech', 'Statistics', 'Biology', 'Physics', 'Microbiology']

export default function CoursesPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [activeCategory, setActiveCategory] = useState('All')

  const filteredCourses = allCourses.filter((course) => {
    const matchesSearch = course.name.toLowerCase().includes(searchQuery.toLowerCase()) || course.code.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesCategory = activeCategory === 'All' || course.category === activeCategory
    return matchesSearch && matchesCategory
  })

  return (
    <DashboardLayout>
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
          <div>
            <h2 className="text-2xl font-bold text-black">My Courses</h2>
            <p className="text-sm text-gray-500 mt-0.5">Browse and manage your enrolled courses</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type="text"
                placeholder="Search courses..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="input-field pl-10 w-64"
              />
            </div>
            <button className="p-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
              <Filter className="w-4 h-4 text-gray-500" />
            </button>
          </div>
        </div>

        {/* Category Filters */}
        <div className="flex gap-2 overflow-x-auto scrollbar-hide mb-6 pb-2">
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setActiveCategory(cat)}
              className={`px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all ${
                activeCategory === cat
                  ? 'bg-[#D10000] text-white'
                  : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* Course Grid */}
        <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
          {filteredCourses.map((course, index) => (
            <div key={index} className="card-elevated bg-white rounded-3xl overflow-hidden group cursor-pointer">
              {/* Course Image */}
              <div className="relative h-40 overflow-hidden">
                <img
                  src={course.image}
                  alt={course.name}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                <div className="absolute bottom-4 left-4 right-4">
                  <span className="text-[10px] font-mono-data font-bold bg-[#D10000] text-white px-2 py-0.5 rounded">
                    {course.code}
                  </span>
                  <h3 className="text-white font-semibold mt-1 text-sm truncate">{course.name}</h3>
                </div>
              </div>

              {/* Course Info */}
              <div className="p-5">
                <div className="flex items-center justify-between mb-3">
                  <div className="flex items-center gap-2 text-xs text-gray-500">
                    <Users className="w-3.5 h-3.5" />
                    <span>{course.students} students</span>
                  </div>
                  <div className="flex items-center gap-1">
                    <Star className="w-3.5 h-3.5 text-amber-400 fill-amber-400" />
                    <span className="text-xs font-medium text-gray-700">{course.rating}</span>
                  </div>
                </div>

                <div className="flex items-center gap-4 text-xs text-gray-500 mb-4">
                  <span className="flex items-center gap-1">
                    <BookOpen className="w-3.5 h-3.5" />
                    {course.credits} Credits
                  </span>
                  <span className="flex items-center gap-1">
                    <Clock className="w-3.5 h-3.5" />
                    {course.lecturer}
                  </span>
                </div>

                {/* Progress */}
                <div className="mb-4">
                  <div className="flex items-center justify-between text-xs mb-1.5">
                    <span className="text-gray-500">Progress</span>
                    <span className="font-mono-data font-semibold text-[#D10000]">{course.progress}%</span>
                  </div>
                  <div className="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div
                      className="h-full rounded-full transition-all duration-500"
                      style={{ width: `${course.progress}%`, backgroundColor: '#D10000' }}
                    />
                  </div>
                </div>

                <button className="w-full py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:border-[#D10000]/30 hover:bg-red-50 hover:text-[#D10000] transition-all flex items-center justify-center gap-2 group/btn">
                  Continue Learning
                  <ChevronRight className="w-4 h-4 group-hover/btn:translate-x-0.5 transition-transform" />
                </button>
              </div>
            </div>
          ))}
        </div>

        {filteredCourses.length === 0 && (
          <div className="text-center py-16">
            <BookOpen className="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-gray-500">No courses found</h3>
            <p className="text-sm text-gray-400 mt-1">Try adjusting your search or filters</p>
          </div>
        )}
      </div>
    </DashboardLayout>
  )
}
