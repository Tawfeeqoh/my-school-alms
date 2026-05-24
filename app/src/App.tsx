import { Routes, Route } from 'react-router-dom'
import HomePage from './pages/HomePage'
import SignupPage from './pages/SignupPage'
import OnboardingPage from './pages/OnboardingPage'
import RolePage from './pages/RolePage'
import RegisterStudentPage from './pages/RegisterStudentPage'
import RegisterLecturerPage from './pages/RegisterLecturerPage'
import RegisterInstitutionPage from './pages/RegisterInstitutionPage'
import DashboardPage from './pages/DashboardPage'
import CoursesPage from './pages/CoursesPage'
import AnalyticsPage from './pages/AnalyticsPage'
import AIAssistantPage from './pages/AIAssistantPage'
import NotificationsPage from './pages/NotificationsPage'
import ProfilePage from './pages/ProfilePage'
import SettingsPage from './pages/SettingsPage'

function App() {
  return (
    <Routes>
      <Route path="/" element={<HomePage />} />
      <Route path="/signup" element={<SignupPage />} />
      <Route path="/onboarding" element={<OnboardingPage />} />
      <Route path="/role" element={<RolePage />} />
      <Route path="/register-student" element={<RegisterStudentPage />} />
      <Route path="/register-lecturer" element={<RegisterLecturerPage />} />
      <Route path="/register-institution" element={<RegisterInstitutionPage />} />
      <Route path="/dashboard" element={<DashboardPage />} />
      <Route path="/courses" element={<CoursesPage />} />
      <Route path="/analytics" element={<AnalyticsPage />} />
      <Route path="/ai-assistant" element={<AIAssistantPage />} />
      <Route path="/notifications" element={<NotificationsPage />} />
      <Route path="/profile" element={<ProfilePage />} />
      <Route path="/settings" element={<SettingsPage />} />
    </Routes>
  )
}

export default App
