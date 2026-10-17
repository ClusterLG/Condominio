// generate_audio_sfx.js
const fs = require('fs');
const path = require('path');

const SAMPLE_RATE = 44100;

function createWavBuffer(samples, sampleRate = SAMPLE_RATE) {
  const numChannels = 1;
  const bitsPerSample = 16;
  const byteRate = (sampleRate * numChannels * bitsPerSample) / 8;
  const blockAlign = (numChannels * bitsPerSample) / 8;
  const dataSize = samples.length * 2;
  const buffer = Buffer.alloc(44 + dataSize);

  // RIFF header
  buffer.write('RIFF', 0);
  buffer.writeUInt32LE(36 + dataSize, 4);
  buffer.write('WAVE', 8);

  // fmt subchunk
  buffer.write('fmt ', 12);
  buffer.writeUInt32LE(16, 16); // SubChunk1Size
  buffer.writeUInt16LE(1, 20);  // PCM format
  buffer.writeUInt16LE(numChannels, 22);
  buffer.writeUInt32LE(sampleRate, 24);
  buffer.writeUInt32LE(byteRate, 28);
  buffer.writeUInt16LE(blockAlign, 32);
  buffer.writeUInt16LE(bitsPerSample, 34);

  // data subchunk
  buffer.write('data', 36);
  buffer.writeUInt32LE(dataSize, 40);

  // Write samples
  for (let i = 0; i < samples.length; i++) {
    let s = Math.max(-1, Math.min(1, samples[i]));
    let intVal = s < 0 ? s * 0x8000 : s * 0x7FFF;
    buffer.writeInt16LE(Math.floor(intVal), 44 + i * 2);
  }

  return buffer;
}

const outDir = path.join(__dirname, 'public');
if (!fs.existsSync(outDir)) {
  fs.mkdirSync(outDir, { recursive: true });
}

// 1. WHOOSH / SWOOSH (0.5 seconds motion whoosh with bandpassed noise & frequency glide)
function generateWhoosh() {
  const duration = 0.55;
  const totalSamples = Math.floor(duration * SAMPLE_RATE);
  const samples = new Float32Array(totalSamples);
  let b0 = 0, b1 = 0, b2 = 0;

  for (let i = 0; i < totalSamples; i++) {
    const t = i / totalSamples;
    // Volume envelope: rise then fall
    const envelope = Math.sin(Math.PI * t) ** 1.8;
    // White noise
    const white = Math.random() * 2 - 1;
    // Lowpass filter whose cutoff sweeps from 300Hz up to 2500Hz then down to 400Hz
    const cutoff = 300 + 2200 * Math.sin(Math.PI * t);
    const rc = 1.0 / (cutoff * 2 * Math.PI);
    const dt = 1.0 / SAMPLE_RATE;
    const alpha = dt / (rc + dt);
    b0 = b0 + alpha * (white - b0);
    // Sine sub-bass sweep under the noise
    const subFreq = 80 + 180 * Math.sin(Math.PI * t);
    const sub = Math.sin((2 * Math.PI * subFreq * i) / SAMPLE_RATE) * 0.35;
    samples[i] = (b0 * 0.75 + sub) * envelope * 0.85;
  }
  return samples;
}

// 2. POP / TOCK (snappy UI pop for card reveals, 0.08 seconds)
function generatePop() {
  const duration = 0.09;
  const totalSamples = Math.floor(duration * SAMPLE_RATE);
  const samples = new Float32Array(totalSamples);
  let phase = 0;

  for (let i = 0; i < totalSamples; i++) {
    const t = i / totalSamples;
    const env = (1 - t) ** 2.5;
    // Pitch sweeps from 900Hz down to 120Hz
    const freq = 900 * (1 - t * 0.85);
    phase += (2 * Math.PI * freq) / SAMPLE_RATE;
    samples[i] = Math.sin(phase) * env * 0.8;
  }
  return samples;
}

// 3. SUCCESS DING / CHIME (1.2 seconds crystalline twin harmonic bell)
function generateDing() {
  const duration = 1.2;
  const totalSamples = Math.floor(duration * SAMPLE_RATE);
  const samples = new Float32Array(totalSamples);

  const f1 = 1046.5; // C6
  const f2 = 1318.5; // E6
  const f3 = 2093.0; // C7

  for (let i = 0; i < totalSamples; i++) {
    const t = i / SAMPLE_RATE;
    const env = Math.exp(-3.5 * t);
    const s1 = Math.sin(2 * Math.PI * f1 * t) * 0.5;
    const s2 = Math.sin(2 * Math.PI * f2 * t) * 0.35;
    const s3 = Math.sin(2 * Math.PI * f3 * t) * 0.15;
    samples[i] = (s1 + s2 + s3) * env * 0.8;
  }
  return samples;
}

// 4. TICK / SNAP (sharp 0.04s transient click)
function generateTick() {
  const duration = 0.045;
  const totalSamples = Math.floor(duration * SAMPLE_RATE);
  const samples = new Float32Array(totalSamples);

  for (let i = 0; i < totalSamples; i++) {
    const t = i / totalSamples;
    const env = (1 - t) ** 3;
    const freq = 1800 * (1 - t * 0.7);
    const noise = (Math.random() * 2 - 1) * 0.25;
    samples[i] = (Math.sin((2 * Math.PI * freq * i) / SAMPLE_RATE) + noise) * env * 0.7;
  }
  return samples;
}

// 5. RISER / SWELL (1.0 second deep impact riser for transitions)
function generateRiser() {
  const duration = 0.95;
  const totalSamples = Math.floor(duration * SAMPLE_RATE);
  const samples = new Float32Array(totalSamples);
  let phase = 0;

  for (let i = 0; i < totalSamples; i++) {
    const t = i / totalSamples;
    const env = t ** 2; // slow rise, sharp cut
    const freq = 60 + 350 * (t ** 2);
    phase += (2 * Math.PI * freq) / SAMPLE_RATE;
    const noise = (Math.random() * 2 - 1) * t * 0.35;
    samples[i] = (Math.sin(phase) + noise) * env * 0.75;
  }
  return samples;
}

// 6. TECH AMBIENT BEAT (61 seconds continuous background music loop)
function generateTechMusicBed() {
  const duration = 61.0;
  const totalSamples = Math.floor(duration * SAMPLE_RATE);
  const samples = new Float32Array(totalSamples);

  const bpm = 118;
  const beatDuration = 60 / bpm; // ~0.508s
  const barDuration = beatDuration * 4; // ~2.034s

  // Chord progression: Am -> F -> C -> G
  const chordRoots = [220, 174.6, 261.6, 196]; // A3, F3, C4, G3

  for (let i = 0; i < totalSamples; i++) {
    const t = i / SAMPLE_RATE;
    const barIndex = Math.floor((t / barDuration) % 4);
    const timeInBar = t % barDuration;
    const timeInBeat = t % beatDuration;
    const beatFraction = timeInBeat / beatDuration;

    // 1. Kick on beats 0, 1, 2, 3
    let kick = 0;
    if (beatFraction < 0.25) {
      const kEnv = (1 - beatFraction / 0.25) ** 3;
      const kFreq = 120 * (1 - beatFraction * 3) + 40;
      kick = Math.sin(2 * Math.PI * kFreq * timeInBeat) * kEnv * 0.45;
    }

    // 2. Hi-hat on off-beats (half-beats)
    let hihat = 0;
    const halfBeatFraction = (t % (beatDuration / 2)) / (beatDuration / 2);
    if (halfBeatFraction < 0.15 && beatFraction > 0.3) {
      const hEnv = (1 - halfBeatFraction / 0.15) ** 4;
      hihat = (Math.random() * 2 - 1) * hEnv * 0.12;
    }

    // 3. Synth Bassline
    const root = chordRoots[barIndex] / 2; // deep bass
    const bassEnv = Math.exp(-2.2 * (timeInBeat % beatDuration));
    const bass = (Math.sin(2 * Math.PI * root * t) + 0.3 * Math.sin(4 * Math.PI * root * t)) * bassEnv * 0.28;

    // 4. Soft atmospheric synth pad
    const pad1 = Math.sin(2 * Math.PI * chordRoots[barIndex] * t);
    const pad2 = Math.sin(2 * Math.PI * (chordRoots[barIndex] * 1.25) * t); // major/minor 3rd approx
    const pad3 = Math.sin(2 * Math.PI * (chordRoots[barIndex] * 1.5) * t);  // 5th
    const padEnv = 0.5 + 0.5 * Math.sin(Math.PI * (timeInBar / barDuration));
    const pad = (pad1 + pad2 + pad3) * 0.08 * padEnv;

    // Master fade in (0-1.5s) and fade out (58.5-60.5s)
    let masterEnv = 1;
    if (t < 1.5) masterEnv = t / 1.5;
    if (t > 58.5) masterEnv = Math.max(0, (60.5 - t) / 2.0);

    samples[i] = (kick + hihat + bass + pad) * masterEnv * 0.85;
  }
  return samples;
}

fs.writeFileSync(path.join(outDir, 'whoosh.wav'), createWavBuffer(generateWhoosh()));
fs.writeFileSync(path.join(outDir, 'pop.wav'), createWavBuffer(generatePop()));
fs.writeFileSync(path.join(outDir, 'ding.wav'), createWavBuffer(generateDing()));
fs.writeFileSync(path.join(outDir, 'tick.wav'), createWavBuffer(generateTick()));
fs.writeFileSync(path.join(outDir, 'riser.wav'), createWavBuffer(generateRiser()));
fs.writeFileSync(path.join(outDir, 'tech_ambient.wav'), createWavBuffer(generateTechMusicBed()));

console.log('✅ Generated 6 crystal-clear audio WAV files in public/:');
console.log(' - whoosh.wav (Swoosh transition)');
console.log(' - pop.wav (UI appearance pop)');
console.log(' - ding.wav (Crystalline success bell)');
console.log(' - tick.wav (Crisp transient click)');
console.log(' - riser.wav (Deep motion riser)');
console.log(' - tech_ambient.wav (45s rhythmic modern tech background music)');
