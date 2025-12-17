package utils

import (
	"runtime"
)

// GetMemoryStats returns current memory usage statistics
func GetMemoryStats() (current, peak uint64) {
	var m runtime.MemStats
	runtime.ReadMemStats(&m)
	return m.Alloc, m.TotalAlloc
}
