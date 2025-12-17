package types

import "time"

// TestResponse represents the response structure for test endpoints
type TestResponse struct {
	PID              int     `json:"pid"`
	MemoryUsage      uint64  `json:"memory_usage"`
	MemoryPeak       uint64  `json:"memory_peak"`
	ExecutionTimeMs  float64 `json:"execution_time_ms"`
	Timestamp        string  `json:"timestamp"`
	Load             string  `json:"load,omitempty"`
}

// NewTestResponse creates a new test response
func NewTestResponse(pid int, memoryUsage, memoryPeak uint64, executionTime time.Duration, load string) *TestResponse {
	return &TestResponse{
		PID:             pid,
		MemoryUsage:     memoryUsage,
		MemoryPeak:      memoryPeak,
		ExecutionTimeMs: float64(executionTime.Nanoseconds()) / 1e6, // Convert to milliseconds
		Timestamp:       time.Now().Format("2006-01-02 15:04:05"),
		Load:            load,
	}
}

