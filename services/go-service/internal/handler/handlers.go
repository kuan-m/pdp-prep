package handler

import (
	"encoding/json"
	"net/http"
	"os"
	"time"

	"pdp-prep/services/go-service/internal/utils"
	"pdp-prep/services/go-service/pkg/types"
)

func HandleTest(w http.ResponseWriter, r *http.Request) {
	startTime := time.Now()

	_ = utils.ComputeHeavy()

	executionTime := time.Since(startTime)

	memoryUsage, memoryPeak := utils.GetMemoryStats()

	response := types.NewTestResponse(
		os.Getpid(),
		memoryUsage,
		memoryPeak,
		executionTime,
		"",
	)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(response)
}

func HandleTestLight(w http.ResponseWriter, r *http.Request) {
	startTime := time.Now()

	_ = utils.ComputeLight()

	executionTime := time.Since(startTime)

	memoryUsage, memoryPeak := utils.GetMemoryStats()

	response := types.NewTestResponse(
		os.Getpid(),
		memoryUsage,
		memoryPeak,
		executionTime,
		"light",
	)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(response)
}
