import { useState } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import {
  LayoutDashboard,
  BookOpen,
  CalendarCheck,
  Brain,
  BarChart3,
  Bell,
  Settings,
  User,
  LogOut,
  GraduationCap,
  Menu,
  X,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react'

const navItems = [
  { icon: LayoutDashboard, label: 'Dashboard', path: '/dashboard' },
  { icon: BookOpen, label: 'Courses', path: '/courses' },
  { icon: CalendarCheck, label: 'Attendance', path: '/dashboard' },
  { icon: Brain, label: 'AI Assistant', path: '/ai-assistant' },
  { icon: BarChart3, label: 'Analytics', path: '/analytics' },
  { icon: Bell, label: 'Notifications', path: '/notifications' },
]

const bottomItems = [
  { icon: User, label: 'Profile', path: '/profile' },
  { icon: Settings, label: 'Settings', path: '/settings' },
]

interface DashboardLayoutProps {
  children: React.ReactNode
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const navigate = useNavigate()
  const location = useLocation()
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false)

  const isActive = (path: string) => location.pathname === path

  return (
    <div className="min-h-screen flex" style={{ backgroundColor: '#F5F5F7' }}>
      {/* Mobile sidebar overlay */}
      {mobileSidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 lg:hidden"
          onClick={() => setMobileSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed lg:static inset-y-0 left-0 z-50 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 ${
          mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        } ${sidebarOpen ? 'w-64' : 'w-20'}`}
      >
        {/* Logo */}
        <div className="flex items-center justify-between h-16 px-4 border-b border-gray-100">
          <button
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-3 overflow-hidden"
          >
            <div
              className="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
              style={{ backgroundColor: '#D10000' }}
            >
              <GraduationCap className="w-5 h-5 text-white" />
            </div>
            {sidebarOpen && (
              <div className="animate-fade-in-up">
                <span className="text-sm font-bold text-black leading-tight block">ALMS</span>
                <span className="text-[9px] font-mono-data text-gray-500 tracking-widest uppercase">
                  FCAHPT IBADAN
                </span>
              </div>
            )}
          </button>
          <button
            onClick={() => setMobileSidebarOpen(false)}
            className="lg:hidden p-1.5 rounded-lg hover:bg-gray-100"
          >
            <X className="w-5 h-5 text-gray-500" />
          </button>
        </div>

        {/* Main Nav */}
        <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-hide">
          {navItems.map((item) => (
            <button
              key={item.path + item.label}
              onClick={() => {
                navigate(item.path)
                setMobileSidebarOpen(false)
              }}
              className={`sidebar-link w-full ${isActive(item.path) ? 'active' : ''}`}
              title={!sidebarOpen ? item.label : undefined}
            >
              <item.icon className="w-5 h-5 flex-shrink-0" />
              {sidebarOpen && <span className="truncate">{item.label}</span>}
            </button>
          ))}
        </nav>

        {/* Bottom Nav */}
        <div className="px-3 py-4 border-t border-gray-100 space-y-1">
          {bottomItems.map((item) => (
            <button
              key={item.path}
              onClick={() => {
                navigate(item.path)
                setMobileSidebarOpen(false)
              }}
              className={`sidebar-link w-full ${isActive(item.path) ? 'active' : ''}`}
              title={!sidebarOpen ? item.label : undefined}
            >
              <item.icon className="w-5 h-5 flex-shrink-0" />
              {sidebarOpen && <span>{item.label}</span>}
            </button>
          ))}
          <button
            onClick={() => navigate('/')}
            className="sidebar-link w-full text-red-500 hover:text-red-600 hover:bg-red-50"
            title={!sidebarOpen ? 'Logout' : undefined}
          >
            <LogOut className="w-5 h-5 flex-shrink-0" />
            {sidebarOpen && <span>Logout</span>}
          </button>
        </div>

        {/* User Profile */}
        {sidebarOpen && (
          <div className="px-4 py-3 border-t border-gray-100">
            <button
              onClick={() => navigate('/profile')}
              className="flex items-center gap-3 w-full hover:bg-gray-50 rounded-xl p-2 transition-colors"
            >
              <img
                src="/images/avatar-student.jpg"
                alt="Student"
                className="w-9 h-9 rounded-full object-cover ring-2 ring-gray-100"
              />
              <div className="text-left overflow-hidden">
                <p className="text-sm font-semibold text-black truncate">Amara Okafor</p>
                <p className="text-xs text-gray-500 truncate">ND Computer Science</p>
              </div>
            </button>
          </div>
        )}

        {/* Collapse Toggle */}
        <button
          onClick={() => setSidebarOpen(!sidebarOpen)}
          className="hidden lg:flex absolute -right-3 top-20 w-6 h-6 bg-white border border-gray-200 rounded-full items-center justify-center shadow-sm hover:shadow-md transition-shadow"
        >
          {sidebarOpen ? (
            <ChevronLeft className="w-3 h-3 text-gray-500" />
          ) : (
            <ChevronRight className="w-3 h-3 text-gray-500" />
          )}
        </button>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top Bar */}
        <header className="h-16 bg-white/80 backdrop-blur-xl border-b border-gray-200 flex items-center justify-between px-4 md:px-8 sticky top-0 z-30">
          <div className="flex items-center gap-4">
            <button
              onClick={() => setMobileSidebarOpen(true)}
              className="lg:hidden p-2 rounded-lg hover:bg-gray-100"
            >
              <Menu className="w-5 h-5 text-gray-600" />
            </button>
            <div>
              <h1 className="text-lg md:text-xl font-bold text-black">
                Welcome back, <span className="font-normal">Amara</span>
              </h1>
              <p className="text-xs font-mono-data text-gray-500 hidden sm:block">
                {new Date().toLocaleDateString('en-US', {
                  weekday: 'short',
                  year: 'numeric',
                  month: 'short',
                  day: 'numeric',
                }).toUpperCase()}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => navigate('/notifications')}
              className="relative p-2 rounded-xl hover:bg-gray-100 transition-colors"
            >
              <Bell className="w-5 h-5 text-gray-600" />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-[#D10000] rounded-full ring-2 ring-white" />
            </button>
            <button
              onClick={() => navigate('/profile')}
              className="w-9 h-9 rounded-full overflow-hidden ring-2 ring-gray-100 hover:ring-[#D10000]/30 transition-all"
            >
              <img
                src="/images/avatar-student.jpg"
                alt="Profile"
                className="w-full h-full object-cover"
              />
            </button>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 p-4 md:p-8 overflow-y-auto">
          {children}
        </main>
      </div>
    </div>
  )
}
