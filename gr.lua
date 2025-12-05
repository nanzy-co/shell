local Players = game:GetService("Players")
local LocalPlayer = Players.LocalPlayer
local Lighting = game:GetService("Lighting")
local GuiService = game:GetService("GuiService")

-- FIX KONTOL: Mengakses UserSettings().GameSettings dengan pcall agar tidak crash
local Settings 
local success, result = pcall(function() 
    return UserSettings().GameSettings 
end)
if success then
    Settings = result
end

local function lowGraphics()
    if Settings then -- Cek Settings sebelum dipakai
        Settings.Rendering.QualityLevel = 1
    end
    Lighting.FogEnd = 100000
    Lighting.GlobalShadows = false
    Lighting.Technology = Enum.Technology.Compatibility
    Lighting:SetMinutesAfterMidnight(450)
    for _, obj in ipairs(workspace:GetDescendants()) do
        if obj:IsA("BasePart") and obj.Material ~= Enum.Material.ForceField then
            obj.Material = Enum.Material.Plastic
        end
        if obj:IsA("Decal") or obj:IsA("Texture") then
            obj:Destroy()
        end
    end
end

local function normalGraphics()
    if Settings then -- Cek Settings sebelum dipakai
        Settings.Rendering.QualityLevel = 10
    end
    Lighting.FogEnd = 0
    Lighting.GlobalShadows = true
    Lighting.Technology = Enum.Technology.Future
    Lighting:SetMinutesAfterMidnight(720)
end

local function toggleNightVision()
    local isNight = Lighting.TimeOfDay == "00:00:00" or Lighting.TimeOfDay == "00:00:01" 
    if isNight then
        Lighting.TimeOfDay = "14:00:00"
        Lighting.ColorShift_Bottom = Color3.fromRGB(0, 0, 0)
        Lighting.ColorShift_Top = Color3.fromRGB(0, 0, 0)
    else
        Lighting.TimeOfDay = "00:00:00"
        Lighting.ColorShift_Bottom = Color3.fromRGB(0, 255, 0)
        Lighting.ColorShift_Top = Color3.fromRGB(0, 255, 0)
    end
end

local function setHighFPS()
    -- FIX KONTOL BARIS 66 & 68: Hapus logic Vsync yang salah, pastikan Settings ada
    if Settings then 
        Settings.Rendering.FrameRateManager = Enum.FrameRateManager.Performance
    end
end

setHighFPS() 

local ScreenGui = Instance.new("ScreenGui")
ScreenGui.Name = "PAYAH_MENU_FIXED_KONTOL"
ScreenGui.Parent = LocalPlayer:WaitForChild("PlayerGui")

local Frame = Instance.new("Frame")
Frame.Parent = ScreenGui
Frame.Size = UDim2.new(0, 300, 0, 200)
Frame.Position = UDim2.new(0.5, -150, 0.5, -100)
Frame.BackgroundColor3 = Color3.fromRGB(20, 20, 20)
Frame.BorderSizePixel = 0
Frame.Active = true
Frame.Draggable = true

local Title = Instance.new("TextLabel")
Title.Parent = Frame
Title.Size = UDim2.new(1, 0, 0, 30)
Title.BackgroundColor3 = Color3.fromRGB(0, 100, 200)
Title.TextColor3 = Color3.fromRGB(255, 255, 255)
Title.Text = "PERFORMANCE KONTOL"
Title.Font = Enum.Font.SourceSansBold
Title.TextSize = 18

local LowButton = Instance.new("TextButton")
LowButton.Parent = Frame
LowButton.Size = UDim2.new(0.9, 0, 0, 40)
LowButton.Position = UDim2.new(0.05, 0, 0.25, 0)
LowButton.BackgroundColor3 = Color3.fromRGB(30, 30, 30)
LowButton.TextColor3 = Color3.fromRGB(0, 150, 255)
LowButton.Text = "LOW GRAPHICS"
LowButton.TextSize = 16
LowButton.MouseButton1Click:Connect(lowGraphics)

local NormalButton = Instance.new("TextButton")
NormalButton.Parent = Frame
NormalButton.Size = UDim2.new(0.9, 0, 0, 40)
NormalButton.Position = UDim2.new(0.05, 0, 0.45, 0)
NormalButton.BackgroundColor3 = Color3.fromRGB(30, 30, 30)
NormalButton.TextColor3 = Color3.fromRGB(0, 150, 255)
NormalButton.Text = "NORMAL GRAPHICS"
NormalButton.TextSize = 16
NormalButton.MouseButton1Click:Connect(normalGraphics)

local VisionButton = Instance.new("TextButton")
VisionButton.Parent = Frame
VisionButton.Size = UDim2.new(0.9, 0, 0, 40)
VisionButton.Position = UDim2.new(0.05, 0, 0.65, 0)
VisionButton.BackgroundColor3 = Color3.fromRGB(30, 30, 30)
VisionButton.TextColor3 = Color3.fromRGB(0, 150, 255)
VisionButton.Text = "TOGGLE NIGHT VISION"
VisionButton.TextSize = 16
VisionButton.MouseButton1Click:Connect(toggleNightVision)

Frame.Visible = false

local ToggleButton = Instance.new("TextButton")
ToggleButton.Parent = ScreenGui
ToggleButton.Size = UDim2.new(0, 100, 0, 30)
ToggleButton.Position = UDim2.new(0.1, 0, 0.9, 0)
ToggleButton.BackgroundColor3 = Color3.fromRGB(0, 50, 150)
ToggleButton.TextColor3 = Color3.fromRGB(255, 255, 255)
ToggleButton.Text = "OPEN MENU"
ToggleButton.TextSize = 14
ToggleButton.MouseButton1Click:Connect(function()
    Frame.Visible = not Frame.Visible
    ToggleButton.Text = Frame.Visible and "CLOSE MENU" or "OPEN MENU"
end)
