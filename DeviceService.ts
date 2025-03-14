// src/services/DeviceService.ts
/**
 * Service for detecting device capabilities and optimizing the app accordingly.
 * This service helps the app adjust its rendering and features based on device performance.
 */

 class DeviceService {
  private isMobileDevice: boolean = false;
  private isLowEndDevice: boolean = false;
  private prefersReducedMotion: boolean = false;
  private isTouchDevice: boolean = false;
  private devicePixelRatio: number = 1;
  private subscribers: Array<(deviceInfo: DeviceInfo) => void> = [];

  constructor() {
    this.detectDeviceCapabilities();
    this.setupListeners();
  }

  /**
   * Detects device capabilities on initialization
   */
  private detectDeviceCapabilities(): void {
    // Check if mobile
    this.isMobileDevice = 
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
      window.innerWidth <= 768;

    // Check for touch device
    this.isTouchDevice = 'ontouchstart' in window || 
      navigator.maxTouchPoints > 0 ||
      (navigator as any).msMaxTouchPoints > 0;

    // Get device pixel ratio
    this.devicePixelRatio = window.devicePixelRatio || 1;

    // Check for reduced motion preference
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Heuristic for low-end devices (based on memory and processor indicators)
    this.detectLowEndDevice();
  }

  /**
   * Try to determine if the device is low-end based on available memory and performance
   */
  private detectLowEndDevice(): void {
    try {
      // Check RAM if available (Chrome/Edge only)
      if ('deviceMemory' in navigator) {
        this.isLowEndDevice = (navigator as any).deviceMemory < 4; // Less than 4GB RAM
      }

      // Use hardware concurrency as an indicator
      if ('hardwareConcurrency' in navigator && !this.isLowEndDevice) {
        this.isLowEndDevice = navigator.hardwareConcurrency < 4; // Fewer than 4 cores
      }

      // Fallback to mobile + low DPR as an approximation
      if (!this.isLowEndDevice && this.isMobileDevice && this.devicePixelRatio < 2) {
        this.isLowEndDevice = true;
      }
    } catch (error) {
      console.error('Error detecting device capabilities:', error);
      
      // Default to false if detection fails
      this.isLowEndDevice = false;
    }
  }

  /**
   * Set up listeners for changes in device capabilities (like orientation change)
   */
  private setupListeners(): void {
    // Listen for resize events to update device type
    window.addEventListener('resize', this.handleResize.bind(this));

    // Listen for orientation changes
    window.addEventListener('orientationchange', this.handleResize.bind(this));

    // Listen for reduced motion preference changes
    const motionMediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    motionMediaQuery.addEventListener('change', this.handleReducedMotionChange.bind(this));
  }

  /**
   * Handler for window resize events
   */
  private handleResize(): void {
    const wasMobile = this.isMobileDevice;
    this.isMobileDevice = window.innerWidth <= 768;

    // Only notify if mobile status changed
    if (wasMobile !== this.isMobileDevice) {
      this.notifySubscribers();
    }
  }

  /**
   * Handler for reduced motion preference changes
   */
  private handleReducedMotionChange(event: MediaQueryListEvent): void {
    this.prefersReducedMotion = event.matches;
    this.notifySubscribers();
  }

  /**
   * Notify all subscribers of device capability changes
   */
  private notifySubscribers(): void {
    const deviceInfo = this.getDeviceInfo();
    this.subscribers.forEach(callback => callback(deviceInfo));
  }

  /**
   * Get current device information
   */
  public getDeviceInfo(): DeviceInfo {
    return {
      isMobile: this.isMobileDevice,
      isLowEndDevice: this.isLowEndDevice,
      prefersReducedMotion: this.prefersReducedMotion,
      isTouch: this.isTouchDevice,
      pixelRatio: this.devicePixelRatio,
      viewportWidth: window.innerWidth,
      viewportHeight: window.innerHeight,
      isLandscape: window.innerWidth > window.innerHeight
    };
  }

  /**
   * Apply performance optimizations based on device capabilities
   */
  public applyPerformanceOptimizations(): void {
    const { isMobile, isLowEndDevice, prefersReducedMotion } = this.getDeviceInfo();

    const htmlElement = document.documentElement;
    
    // Apply appropriate CSS classes
    if (isMobile) {
      htmlElement.classList.add('mobile-device');
    } else {
      htmlElement.classList.remove('mobile-device');
    }
    
    if (isLowEndDevice) {
      htmlElement.classList.add('low-end-device');
    } else {
      htmlElement.classList.remove('low-end-device');
    }
    
    if (prefersReducedMotion) {
      htmlElement.classList.add('reduced-motion');
    } else {
      htmlElement.classList.remove('reduced-motion');
    }

    // Apply specific optimizations for very low-end devices
    if (isLowEndDevice || (isMobile && window.innerWidth < 480)) {
      // Disable heavy background animations
      const heavyElements = document.querySelectorAll('.galaxy-background, .wave-container');
      heavyElements.forEach(el => {
        (el as HTMLElement).style.display = 'none';
      });

      // Reduce shadow effects
      const shadowElements = document.querySelectorAll('.cyber-card, .prompt-container, .music-card');
      shadowElements.forEach(el => {
        (el as HTMLElement).style.boxShadow = 'none';
      });
    }
  }

  /**
   * Subscribe to device capability changes
   */
  public subscribe(callback: (deviceInfo: DeviceInfo) => void): () => void {
    this.subscribers.push(callback);
    
    // Immediately call with current info
    callback(this.getDeviceInfo());
    
    // Return unsubscribe function
    return () => {
      this.subscribers = this.subscribers.filter(cb => cb !== callback);
    };
  }

  /**
   * Get appropriate CSS class for current device
   */
  public getDeviceClass(): string {
    const { isMobile, isLowEndDevice, isLandscape } = this.getDeviceInfo();
    
    let className = '';
    
    if (isMobile) className += 'mobile ';
    if (isLowEndDevice) className += 'low-performance ';
    if (isLandscape) className += 'landscape ';
    
    return className.trim();
  }

  /**
   * Should high-performance animations be enabled?
   */
  public shouldEnableHighPerformanceFeatures(): boolean {
    const { isMobile, isLowEndDevice, prefersReducedMotion } = this.getDeviceInfo();
    return !isLowEndDevice && !prefersReducedMotion && (!isMobile || window.innerWidth >= 768);
  }
}

// Device information interface
export interface DeviceInfo {
  isMobile: boolean;
  isLowEndDevice: boolean;
  prefersReducedMotion: boolean;
  isTouch: boolean;
  pixelRatio: number;
  viewportWidth: number;
  viewportHeight: number;
  isLandscape: boolean;
}

// Singleton instance
const deviceService = new DeviceService();

export default deviceService;